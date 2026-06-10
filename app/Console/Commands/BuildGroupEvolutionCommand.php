<?php

namespace App\Console\Commands;

use App\Models\CrmClass;
use App\Models\CrmGroupEvolutionSnapshot;
use App\Models\CrmRegistration;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
 *   debuts      — students whose first real monthly payment (non-inscription) is in the same
 *                 month as the class START_DATE, regardless of current registration status
 *   ajouts      — students whose first real monthly payment is strictly after class start month
 *   quittants   — students with registration status "Annulé" in this class
 *   changements — students with registration status "Archive" in this class
 *   actifs      — CLASS_COUNT_STUDENTS_ACTIVE from crm_classes.raw_data
 *
 * Note: Début/Ajout are payment-based (not registration START_DATE-based).
 * A student registered in Jan who never paid is neither Début nor Ajout (unpaid).
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
        $rangeEnd = Carbon::today('Africa/Casablanca')->toDateString();

        $this->info("Building group evolution snapshots ({$rangeStart} → {$rangeEnd})");

        $sites = Site::whereNotNull('crm_store_id')
            ->where('crm_store_id', '>', 0)
            ->when(!$this->option('all'), fn($q) => $q->whereIn('crm_store_id', array_map('intval', $this->option('store'))))
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
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.STATUS_NAME')) IN ('En formation', 'En Préparation')")
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
        // Key by raw_data.ID (= LEVEL_SESSION_ID in registrations = crm_registrations.crm_class_id).
        // crm_registrations.crm_class_id = LEVEL_SESSION_ID ?? CLASS_ID, and LEVEL_SESSION_ID
        // equals raw_data.ID on the class record (e.g. 9363), NOT the CLASS_ID column (e.g. 9948).
        $classStartMonths = []; // [raw_data.ID => 'YYYY-MM']
        $rawIdByClassId = []; // [CLASS_ID => raw_data.ID] for upsert lookup
        foreach ($classes as $class) {
            $raw = $class->raw_data ?? [];
            $rawId = isset($raw['ID']) ? (int) $raw['ID'] : null;
            if ($rawId === null) {
                continue;
            }
            $classStartMonths[$rawId] = isset($raw['START_DATE']) ? Carbon::parse($raw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m') : null;
            $rawIdByClassId[(int) $class->class_id] = $rawId;
        }

        $registrations = CrmRegistration::where('crm_store_id', $storeId)->get(['crm_student_id', 'crm_class_id', 'status', 'raw_data']);

        $this->line('   ' . $registrations->count() . ' registrations loaded from local DB');

        // ── All non-inscription monthly payment months per student (store-wide) ──
        // crm_payment_snapshots has no class_id, so we fetch all months per student
        // and then filter per-registration using the registration START_DATE as a floor:
        // "first payment for this class" = earliest payment month >= registration START_DATE.
        // This excludes payments made for previous groups before the student joined this one.
        $allStudentIds = $registrations->pluck('crm_student_id')->unique()->values()->all();

        $payMonthsByStudent = DB::table('crm_payment_snapshots')
            ->where('crm_store_id', $storeId)
            ->whereIn('student_id', $allStudentIds)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.ITEMS_NAME')) NOT LIKE '%inscription%'")
            ->selectRaw('student_id, DATE_FORMAT(effective_date, "%Y-%m") as pay_month')
            ->distinct()
            ->get()
            ->groupBy('student_id') // [student_id => Collection<{pay_month}>]
            ->map(fn($rows) => $rows->pluck('pay_month')->sort()->values()->all());

        $this->line('   ' . $payMonthsByStudent->count() . ' students with payment records');

        $debutsByGroup = [];
        $ajoutsByGroup = [];
        $quittantsByGroup = [];
        $changementsByGroup = [];

        foreach ($registrations as $reg) {
            $sid = (int) $reg->crm_student_id;
            $cid = (int) $reg->crm_class_id;
            $status = $reg->status;
            $classYm = $classStartMonths[$cid] ?? null;
            $raw = is_array($reg->raw_data) ? $reg->raw_data : json_decode($reg->raw_data, true);

            // Status buckets are independent of payment timing.
            if ($status === 'Annulé') {
                $quittantsByGroup[$cid][$sid] = true;
            } elseif ($status === 'Archive') {
                $changementsByGroup[$cid][$sid] = true;
            }

            if (!$classYm) {
                continue;
            }

            // Registration START_DATE = when the student joined this specific class.
            // First payment for THIS class = earliest month >= registration start date.
            // This avoids attributing payments made for prior groups to this class.
            $regYm = isset($raw['START_DATE']) ? Carbon::parse($raw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m') : $classYm;
            $months = $payMonthsByStudent->get($sid, []);
            $firstForClass = collect($months)->first(fn($m) => $m >= $regYm);

            if (!$firstForClass) {
                // No payment yet but Active → count as Début if enrolled at/before group start month
                if ($status === 'Active' && $regYm <= $classYm) {
                    $debutsByGroup[$cid][$sid] = true;
                }
                continue;
            }

            if ($firstForClass <= $classYm) {
                $debutsByGroup[$cid][$sid] = true;
            } else {
                $ajoutsByGroup[$cid][$sid] = true;
            }
        }

        $now = now();
        $upserts = [];

        foreach ($classes as $class) {
            $cid = (int) $class->class_id;
            $raw = $class->raw_data ?? [];
            $rawId = $rawIdByClassId[$cid] ?? null;
            $startYm = $rawId !== null ? $classStartMonths[$rawId] ?? null : null;

            $debuts = count($debutsByGroup[$rawId] ?? []);
            $ajouts = count($ajoutsByGroup[$rawId] ?? []);
            $quittants = count($quittantsByGroup[$rawId] ?? []);
            $changements = count($changementsByGroup[$rawId] ?? []);

            // Parse ISO datetime from API (e.g. "2026-04-23T23:00:00.000Z") → plain date
            $classStartDate = isset($raw['START_DATE']) ? Carbon::parse($raw['START_DATE'])->setTimezone('Africa/Casablanca')->toDateString() : null;
            $classEndDate = isset($raw['END_DATE']) ? Carbon::parse($raw['END_DATE'])->setTimezone('Africa/Casablanca')->toDateString() : null;

            $upserts[] = [
                'crm_store_id' => $storeId,
                'class_id' => $cid,
                'class_name' => $class->name ?? "#{$cid}",
                'class_start_date' => $classStartDate,
                'class_end_date' => $classEndDate,
                'class_start_month' => $startYm,
                'debuts' => $debuts,
                'ajouts' => $ajouts,
                'quittants' => $quittants,
                'changements' => $changements,
                'actifs' => (int) ($raw['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0),
                'range_start' => $rangeStart,
                'range_end' => $rangeEnd,
                'computed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($upserts, 100) as $chunk) {
            CrmGroupEvolutionSnapshot::upsert($chunk, ['crm_store_id', 'class_id', 'range_start', 'range_end'], ['class_name', 'class_start_date', 'class_end_date', 'class_start_month', 'debuts', 'ajouts', 'quittants', 'changements', 'actifs', 'computed_at', 'updated_at']);
        }

        return count($upserts);
    }
}
