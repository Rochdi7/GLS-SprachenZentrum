<?php

namespace App\Console\Commands;

use App\Models\CrmClass;
use App\Models\CrmCollectionRow;
use App\Models\CrmGroupEvolutionSnapshot;
use App\Models\CrmPaymentAllocation;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Computes group evolution buckets from local mirror tables.
 *
 * WHY THIS COMMAND EXISTS:
 * GroupEvolutionService::build() previously called /payment-allocations live
 * during HTTP requests (40 pages × 25 rows = 1,000 API calls per load).
 * This caused 5–300 second page loads and frequent PHP timeouts.
 *
 * This command reads exclusively from local MySQL tables — zero API calls —
 * and writes precomputed results to crm_group_evolution_snapshot.
 * The dashboard then reads one SELECT < 80ms.
 *
 * Prerequisites (run these first):
 *   php artisan crm:sync-payment-allocations --all
 *   (classes must also be mirrored via homeschool:mirror-core)
 *
 * Usage:
 *   php artisan crm:build-group-evolution --all
 *   php artisan crm:build-group-evolution --store=1234
 *
 * The five buckets (matching the UI labels):
 *   debuts      — students whose first allocation month = class START_DATE month
 *   ajouts      — students who joined after class started (or have "inscription" service)
 *   quittants   — students with unpaid collection rows (not transfers)
 *   changements — students archived in this class but active in another (transfers)
 *   actifs      — CLASS_COUNT_STUDENTS_ACTIVE from crm_classes.raw_data
 */
class BuildGroupEvolutionCommand extends Command
{
    protected $signature = 'crm:build-group-evolution
        {--all      : All configured stores}
        {--store=*  : Specific store IDs}
        {--months=6 : Lookback window for payment allocations (default 6)}';

    protected $description = 'Precompute group evolution snapshot from local tables (zero API calls)';

    public function handle(): int
    {
        $months     = max(1, min(12, (int) $this->option('months')));
        $rangeStart = Carbon::today('Africa/Casablanca')
            ->subMonths($months)->startOfMonth()->toDateString();
        $rangeEnd   = Carbon::today('Africa/Casablanca')->toDateString();

        $this->info("Building group evolution snapshots ({$rangeStart} → {$rangeEnd})");

        $sites = Site::whereNotNull('crm_store_id')->where('crm_store_id', '>', 0)
            ->when(!$this->option('all'), fn ($q) =>
                $q->whereIn('crm_store_id', array_map('intval', $this->option('store')))
            )
            ->get(['crm_store_id', 'name']);

        if ($sites->isEmpty()) {
            $this->warn('No sites found. Use --all or --store=ID.');
            return self::SUCCESS;
        }

        foreach ($sites as $site) {
            $storeId = (int) $site->crm_store_id;
            $this->info("[#{$storeId}] {$site->name}");

            try {
                $count = $this->computeForStore($storeId, $rangeStart, $rangeEnd);
                $this->info("[#{$storeId}] {$count} group snapshots written");
            } catch (\Throwable $e) {
                $this->error("[#{$storeId}] FAILED: {$e->getMessage()}");
                Log::error("crm:build-group-evolution #{$storeId}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    private function computeForStore(int $storeId, string $rangeStart, string $rangeEnd): int
    {
        // Load classes from the local mirror — zero API calls
        $classes = CrmClass::where('site_id', $storeId)
            ->whereNotNull('class_id')
            ->get();

        if ($classes->isEmpty()) {
            $this->line("   No classes found for store #{$storeId}");
            return 0;
        }

        // Load all allocations for this store+range from the local mirror
        // This replaces 40 HTTP calls with one local DB query
        $allocations = CrmPaymentAllocation::where('crm_store_id', $storeId)
            ->where('allocation_date', '>=', $rangeStart)
            ->where('allocation_date', '<=', $rangeEnd)
            ->get(['student_id', 'class_id', 'allocation_month', 'is_inscription']);

        $this->line("   " . $allocations->count() . " allocations loaded from local DB");

        // Build lookup maps (all in PHP memory — no further DB queries for this step)
        // [student_id => [class_id => ['YYYY-MM' => true]]]
        $studentMonths    = [];
        // [student_id => [class_id => true]]
        $inscriptionFlags = [];

        foreach ($allocations as $alloc) {
            $sid = $alloc->student_id;
            $cid = (int) $alloc->class_id;
            $studentMonths[$sid][$cid][$alloc->allocation_month] = true;
            if ($alloc->is_inscription) {
                $inscriptionFlags[$sid][$cid] = true;
            }
        }

        // Build archived/active student maps from raw_data stored during class mirror
        $archivedMap = [];
        $activeMap   = [];
        foreach ($classes as $class) {
            $cid             = (int) $class->class_id;
            $raw             = $class->raw_data ?? [];
            $archivedMap[$cid] = $this->parseStudentList($raw['LIST_STUDENT_ARCHIVED'] ?? []);
            $activeMap[$cid]   = $this->parseStudentList($raw['LIST_STUDENT_ACTIVE']   ?? []);
        }

        // Detect changements: archived in class A, currently active in class B
        $changementsByGroup = $this->detectChangements($studentMonths, $archivedMap, $activeMap);

        // Detect quittants from local crm_collection_rows (zero API calls)
        $quittantsByGroup = $this->detectQuittants(
            $storeId, $rangeStart, $rangeEnd, $studentMonths, $changementsByGroup
        );

        $now     = now();
        $upserts = [];

        foreach ($classes as $class) {
            $cid     = (int) $class->class_id;
            $raw     = $class->raw_data ?? [];
            $startYm = isset($raw['START_DATE'])
                ? Carbon::parse($raw['START_DATE'])->format('Y-m')
                : null;

            $debuts = $ajouts = $quittants = $changements = 0;

            foreach ($studentMonths as $sid => $byClass) {
                if (!isset($byClass[$cid])) continue;

                // Inscription service tag → always Ajout (regardless of start date)
                if (isset($inscriptionFlags[$sid][$cid])) {
                    $ajouts++;
                } elseif ($startYm && isset($byClass[$cid][$startYm])) {
                    // First allocation in the class start month → Début (founding member)
                    $debuts++;
                } else {
                    // All payments after start month → Ajout (joined later)
                    $ajouts++;
                }

                if (isset($quittantsByGroup[$cid][$sid]))   $quittants++;
                if (isset($changementsByGroup[$cid][$sid])) $changements++;
            }

            $upserts[] = [
                'crm_store_id'     => $storeId,
                'class_id'         => $cid,
                'class_name'       => $class->name ?? "#{$cid}",
                'class_start_date' => $raw['START_DATE'] ?? null,
                'class_end_date'   => $raw['END_DATE']   ?? null,
                'class_start_month' => $startYm,
                'debuts'           => $debuts,
                'ajouts'           => $ajouts,
                'quittants'        => $quittants,
                'changements'      => $changements,
                'actifs'           => (int) ($raw['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0),
                'range_start'      => $rangeStart,
                'range_end'        => $rangeEnd,
                'computed_at'      => $now,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        foreach (array_chunk($upserts, 100) as $chunk) {
            CrmGroupEvolutionSnapshot::upsert(
                $chunk,
                ['crm_store_id', 'class_id', 'range_start', 'range_end'],
                [
                    'class_name', 'class_start_date', 'class_end_date', 'class_start_month',
                    'debuts', 'ajouts', 'quittants', 'changements', 'actifs',
                    'computed_at', 'updated_at',
                ]
            );
        }

        return count($upserts);
    }

    /**
     * A student is a Changement if they are archived in class A
     * AND currently active in a different class B.
     */
    private function detectChangements(
        array $studentMonths,
        array $archivedMap,
        array $activeMap,
    ): array {
        $result = [];

        foreach ($studentMonths as $sid => $_) {
            // Classes where this student appears in LIST_STUDENT_ARCHIVED
            $archivedIn = [];
            foreach ($archivedMap as $cid => $ids) {
                if (in_array($sid, $ids, true)) {
                    $archivedIn[] = $cid;
                }
            }
            if (empty($archivedIn)) continue;

            // Check if they are active in any OTHER class
            foreach ($activeMap as $cid => $ids) {
                if (in_array($sid, $ids, true) && !in_array($cid, $archivedIn, true)) {
                    foreach ($archivedIn as $archivedCid) {
                        $result[$archivedCid][$sid] = true;
                    }
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * A student is a Quittant if they have unpaid collection rows in the range
     * and are not already classified as a Changement.
     */
    private function detectQuittants(
        int    $storeId,
        string $start,
        string $end,
        array  $studentMonths,
        array  $changements,
    ): array {
        // Mover IDs are excluded — transfers are not quittants
        $moverIds = [];
        foreach ($changements as $byStudent) {
            foreach ($byStudent as $sid => $_) {
                $moverIds[$sid] = true;
            }
        }

        $rows = CrmCollectionRow::where('crm_store_id', $storeId)
            ->where('registration_status_name', 'Active')
            ->whereBetween('due_date', [$start, $end])
            ->where('rest_amount', '>', 0)
            ->get(['student_id', 'class_id']);

        $result = [];
        foreach ($rows as $row) {
            $sid = $row->student_id;
            $cid = (int) $row->class_id;
            if (!$sid || !$cid) continue;
            if (isset($moverIds[$sid])) continue;
            if (!isset($studentMonths[$sid][$cid])) continue;
            $result[$cid][$sid] = true;
        }

        return $result;
    }

    private function parseStudentList(mixed $list): array
    {
        if (is_array($list)) {
            return array_map(fn ($s) => (int) ($s['STUDENT_ID'] ?? $s['ID'] ?? $s), $list);
        }
        if (is_string($list) && !empty(trim($list))) {
            try {
                $decoded = json_decode($list, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    return array_map(fn ($s) => (int) ($s['STUDENT_ID'] ?? $s['ID'] ?? $s), $decoded);
                }
            } catch (\Throwable) {
            }
            return array_map(fn ($p) => (int) trim($p), explode(',', $list));
        }
        return [];
    }
}
