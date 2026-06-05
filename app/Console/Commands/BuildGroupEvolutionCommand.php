<?php

namespace App\Console\Commands;

use App\Models\CrmClass;
use App\Models\CrmGroupEvolutionSnapshot;
use App\Models\CrmPaymentAllocation;
use App\Models\CrmRegistration;
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
 *   quittants   — students with registration status "Annulé" in this class
 *   changements — students with registration status "Archive" in this class
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

        // Build quittants/changements from registrations — same logic as the drill endpoint.
        // quittant  = registration status "Annulé"
        // changement = registration status "Archive"
        $classIds = $classes->pluck('class_id')->map(fn ($id) => (int) $id)->all();

        $registrations = CrmRegistration::where('crm_store_id', $storeId)
            ->whereIn('crm_class_id', $classIds)
            ->whereIn('status', ['Annulé', 'Archive'])
            ->get(['crm_student_id', 'crm_class_id', 'status']);

        $quittantsByGroup   = [];
        $changementsByGroup = [];
        foreach ($registrations as $reg) {
            $sid = (int) $reg->crm_student_id;
            $cid = (int) $reg->crm_class_id;
            if ($reg->status === 'Annulé') {
                $quittantsByGroup[$cid][$sid]   = true;
            } else {
                $changementsByGroup[$cid][$sid] = true;
            }
        }

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
            }

            // Quittants and changements come from registration status (same logic as drill endpoint).
            // These are counted independently of payment allocations — an annulé student
            // may have no allocations in the current range.
            $quittants   = count($quittantsByGroup[$cid]   ?? []);
            $changements = count($changementsByGroup[$cid] ?? []);

            // Parse ISO datetime from API (e.g. "2026-04-23T23:00:00.000Z") → plain date
            $classStartDate = isset($raw['START_DATE'])
                ? Carbon::parse($raw['START_DATE'])->toDateString()
                : null;
            $classEndDate = isset($raw['END_DATE'])
                ? Carbon::parse($raw['END_DATE'])->toDateString()
                : null;

            $upserts[] = [
                'crm_store_id'      => $storeId,
                'class_id'          => $cid,
                'class_name'        => $class->name ?? "#{$cid}",
                'class_start_date'  => $classStartDate,
                'class_end_date'    => $classEndDate,
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

}
