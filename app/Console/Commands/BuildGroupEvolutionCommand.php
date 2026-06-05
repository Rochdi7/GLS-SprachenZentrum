<?php

namespace App\Console\Commands;

use App\Models\CrmClass;
use App\Models\CrmGroupEvolutionSnapshot;
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
 * Prerequisite: classes must be mirrored (homeschool:mirror-core) and registrations
 * synced (crm:sync-registrations --all).
 *
 * Usage:
 *   php artisan crm:build-group-evolution --all
 *   php artisan crm:build-group-evolution --store=1234
 *
 * The five buckets (matching the UI labels):
 *   debuts      — Active registrations with START_DATE month <= class START_DATE month
 *   ajouts      — Active registrations with START_DATE month >  class START_DATE month
 *   quittants   — students with registration status "Annulé" in this class
 *   changements — students with registration status "Archive" in this class
 *   actifs      — CLASS_COUNT_STUDENTS_ACTIVE from crm_classes.raw_data
 */
class BuildGroupEvolutionCommand extends Command
{
    protected $signature = 'crm:build-group-evolution
        {--all     : All configured stores}
        {--store=* : Specific store IDs}';

    protected $description = 'Precompute group evolution snapshot from local tables (zero API calls)';

    public function handle(): int
    {
        $rangeStart = Carbon::today('Africa/Casablanca')->startOfYear()->toDateString();
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
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.STATUS_NAME')) = 'En formation'")
            ->get();

        if ($classes->isEmpty()) {
            $this->line("   No classes found for store #{$storeId}");
            return 0;
        }

        // All buckets come from registrations — same logic as the drill endpoint.
        // debut      = Active, reg START_DATE month <= class START_DATE month
        // ajout      = Active, reg START_DATE month >  class START_DATE month
        // quittant   = Annulé
        // changement = Archive
        // crm_classes.class_id = API field CLASS_ID (e.g. 9505)
        // crm_registrations.crm_class_id = API field ID (e.g. 8995)
        // Must key the start-month map by raw_data.ID to match registrations.
        $classStartMonths = [];   // [raw ID => 'YYYY-MM']
        $classColIdByRawId = [];  // [raw ID => class_id column] for snapshot upsert
        foreach ($classes as $class) {
            $raw   = $class->raw_data ?? [];
            $rawId = isset($raw['ID']) ? (int) $raw['ID'] : null;
            if ($rawId === null) continue;
            $classStartMonths[$rawId]  = isset($raw['START_DATE'])
                ? Carbon::parse($raw['START_DATE'])->format('Y-m')
                : null;
            $classColIdByRawId[$rawId] = (int) $class->class_id;
        }

        $registrations = CrmRegistration::where('crm_store_id', $storeId)
            ->get(['crm_student_id', 'crm_class_id', 'status', 'raw_data']);

        $this->line("   " . $registrations->count() . " registrations loaded from local DB");

        $debutsByGroup      = [];
        $ajoutsByGroup      = [];
        $quittantsByGroup   = [];
        $changementsByGroup = [];

        foreach ($registrations as $reg) {
            $sid     = (int) $reg->crm_student_id;
            $cid     = (int) $reg->crm_class_id;
            $status  = $reg->status;
            $raw     = is_array($reg->raw_data) ? $reg->raw_data : json_decode($reg->raw_data, true);
            $startYm = $classStartMonths[$cid] ?? null;

            if ($status === 'Annulé') {
                $quittantsByGroup[$cid][$sid] = true;
            } elseif ($status === 'Archive') {
                $changementsByGroup[$cid][$sid] = true;
            } else {
                // Active — reg START_DATE <= class start → Début, else Ajout
                $regStartYm = isset($raw['START_DATE'])
                    ? Carbon::parse($raw['START_DATE'])->format('Y-m')
                    : null;

                if ($startYm && $regStartYm && $regStartYm <= $startYm) {
                    $debutsByGroup[$cid][$sid] = true;
                } else {
                    $ajoutsByGroup[$cid][$sid] = true;
                }
            }
        }

        $now     = now();
        $upserts = [];

        foreach ($classes as $class) {
            $cid     = (int) $class->class_id;   // CLASS_ID — used for upsert key
            $raw     = $class->raw_data ?? [];
            $rawId   = isset($raw['ID']) ? (int) $raw['ID'] : null; // ID — matches crm_registrations.crm_class_id
            $startYm = $rawId !== null ? ($classStartMonths[$rawId] ?? null) : null;

            $debuts      = $rawId !== null ? count($debutsByGroup[$rawId]      ?? []) : 0;
            $ajouts      = $rawId !== null ? count($ajoutsByGroup[$rawId]      ?? []) : 0;
            $quittants   = $rawId !== null ? count($quittantsByGroup[$rawId]   ?? []) : 0;
            $changements = $rawId !== null ? count($changementsByGroup[$rawId] ?? []) : 0;

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
