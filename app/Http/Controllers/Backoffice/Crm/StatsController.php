<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmCollectionRow;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Models\Site;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    public function index(Request $request): View
    {
        $months  = max(3, min(12, (int) ($request->query('months', 6))));
        $storeId = $this->currentStrStoreId();

        $cacheKey = "crm.stats.dashboard:{$months}:" . ($storeId ?: 'all');

        $data = Cache::remember($cacheKey, 600, function () use ($months, $storeId) {
            return [
                'encaissement'  => $this->encaissementByCenter($months, $storeId),
                'recouvrement'  => $this->recouvrementByCenter($storeId),
                'registrations' => $this->registrationsByCenter($months, $storeId),
                'comparison'    => $this->periodComparison($storeId),
                'sites'         => Site::whereNotNull('crm_store_id')->orderBy('name')->get(['id', 'name', 'crm_store_id']),
            ];
        });

        return $this->view('backoffice.crm.stats-dashboard.index', array_merge($data, [
            'months'       => $months,
            'storeId'      => $storeId,
            'snapshotDate' => $data['encaissement']['snapshot'] ?? CrmPaymentSnapshot::max('snapshot_date'),
        ]));
    }

    public function encaissementRange(Request $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->query('startDate');
        $endDate   = $request->query('endDate');
        $storeId   = $request->query('strStoreId') ? (int) $request->query('strStoreId') : null;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'startDate et endDate sont requis.'], 422);
        }

        try {
            Carbon::parse($startDate);
            Carbon::parse($endDate);
        } catch (\Throwable) {
            return response()->json(['error' => 'Dates invalides.'], 422);
        }

        $storeFilter = $storeId ? "AND crm_store_id = {$storeId}" : '';

        $rows = CrmPaymentSnapshot::query()
            ->fromRaw("(
                SELECT crm_store_id, date_creation_date, amount
                FROM crm_payment_snapshots s1
                WHERE date_creation_date BETWEEN ? AND ?
                  AND payment_type_id = 1
                  {$storeFilter}
                  AND snapshot_date = (
                      SELECT MAX(s2.snapshot_date)
                      FROM crm_payment_snapshots s2
                      WHERE s2.crm_payment_id = s1.crm_payment_id
                  )
            ) AS deduped", [$startDate, $endDate])
            ->selectRaw('crm_store_id, SUM(amount) as total, COUNT(*) as nb')
            ->groupBy('crm_store_id')
            ->orderByDesc('total')
            ->get();

        $sites = Site::whereNotNull('crm_store_id')
            ->pluck('name', 'crm_store_id');

        $data = $rows->map(fn ($r) => [
            'store_id'   => (int) $r->crm_store_id,
            'store_name' => $sites[$r->crm_store_id] ?? 'Store #' . $r->crm_store_id,
            'total'      => (float) $r->total,
            'nb'         => (int) $r->nb,
        ])->values()->toArray();

        $grandTotal = array_sum(array_column($data, 'total'));
        $grandNb    = array_sum(array_column($data, 'nb'));

        return response()->json([
            'data'        => $data,
            'grand_total' => $grandTotal,
            'grand_nb'    => $grandNb,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'snapshot'    => CrmPaymentSnapshot::max('snapshot_date'),
        ]);
    }

    public function recouvrementRange(Request $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->query('startDate');
        $endDate   = $request->query('endDate');
        $storeId   = $request->query('strStoreId') ? (int) $request->query('strStoreId') : null;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'startDate et endDate sont requis.'], 422);
        }

        $storeFilter = $storeId ? "AND crm_store_id = {$storeId}" : '';

        $rows = DB::select("
            SELECT crm_store_id, store_name,
                   SUM(rest_amount) as total_reste,
                   SUM(total_price) as total_ca,
                   COUNT(*)         as cnt
            FROM crm_collection_rows
            WHERE due_date BETWEEN ? AND ?
              AND rest_amount > 0
              AND registration_status_id != 10
              {$storeFilter}
            GROUP BY crm_store_id, store_name
            ORDER BY total_reste DESC
        ", [$startDate, $endDate]);

        $data = collect($rows)->map(fn ($r) => [
            'store_id'    => (int) $r->crm_store_id,
            'store_name'  => $r->store_name ?? 'Store #' . $r->crm_store_id,
            'total_reste' => (float) $r->total_reste,
            'total_ca'    => (float) $r->total_ca,
            'cnt'         => (int) $r->cnt,
        ])->values()->toArray();

        return response()->json([
            'data'         => $data,
            'grand_reste'  => array_sum(array_column($data, 'total_reste')),
            'grand_ca'     => array_sum(array_column($data, 'total_ca')),
            'grand_cnt'    => array_sum(array_column($data, 'cnt')),
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'snapshot'     => CrmPaymentSnapshot::max('snapshot_date'),
        ]);
    }

    public function comparaison(): View
    {
        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get(['id', 'name', 'crm_store_id']);
        $snapshotDate = CrmPaymentSnapshot::max('snapshot_date');

        return $this->view('backoffice.crm.stats-dashboard.comparaison', compact('sites', 'snapshotDate'));
    }

    public function comparaisonData(Request $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->query('startDate');
        $endDate   = $request->query('endDate');
        $storeIds  = array_filter(array_map('intval', (array) $request->query('stores', [])));
        $groupBy   = in_array($request->query('groupBy'), ['day', 'week', 'month']) ? $request->query('groupBy') : 'day';

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'startDate et endDate requis.'], 422);
        }

        $storeFilter = !empty($storeIds)
            ? 'AND s1.crm_store_id IN (' . implode(',', $storeIds) . ')'
            : '';

        $dateFmt = match ($groupBy) {
            'month' => "DATE_FORMAT(s1.date_creation_date,'%Y-%m')",
            'week'  => "DATE_FORMAT(s1.date_creation_date,'%x-W%v')",
            default => "DATE_FORMAT(s1.date_creation_date,'%Y-%m-%d')",
        };

        $rows = DB::select("
            SELECT
                s1.crm_store_id,
                {$dateFmt} AS period,
                SUM(s1.amount)  AS total,
                COUNT(*)        AS nb
            FROM crm_payment_snapshots s1
            WHERE s1.date_creation_date BETWEEN ? AND ?
              AND s1.payment_type_id = 1
              {$storeFilter}
              AND s1.snapshot_date = (
                  SELECT MAX(s2.snapshot_date)
                  FROM crm_payment_snapshots s2
                  WHERE s2.crm_payment_id = s1.crm_payment_id
              )
            GROUP BY s1.crm_store_id, period
            ORDER BY period, s1.crm_store_id
        ", [$startDate, $endDate]);

        $sites   = Site::whereNotNull('crm_store_id')->pluck('name', 'crm_store_id');
        $periods = collect($rows)->pluck('period')->unique()->sort()->values()->toArray();
        $stores  = collect($rows)->pluck('crm_store_id')->unique()->values()->toArray();

        // pivot: store_id => [period => [total, nb]]
        $pivot = [];
        foreach ($rows as $r) {
            $pivot[$r->crm_store_id][$r->period] = [
                'total' => (float) $r->total,
                'nb'    => (int)   $r->nb,
            ];
        }

        // totals per store
        $storeTotals = [];
        foreach ($stores as $sid) {
            $storeTotals[$sid] = [
                'name'  => $sites[$sid] ?? "Store #{$sid}",
                'total' => array_sum(array_column($pivot[$sid] ?? [], 'total')),
                'nb'    => array_sum(array_column($pivot[$sid] ?? [], 'nb')),
            ];
        }
        uasort($storeTotals, fn($a, $b) => $b['total'] <=> $a['total']);

        return response()->json([
            'periods'      => $periods,
            'stores'       => $storeTotals,
            'pivot'        => $pivot,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'group_by'     => $groupBy,
            'snapshot'     => CrmPaymentSnapshot::max('snapshot_date'),
        ]);
    }

    public function caAnnuel(Request $request): View
    {
        $year    = (int) ($request->query('year', Carbon::now('Africa/Casablanca')->year));
        $storeId = $request->has('store_id')
            ? ($request->query('store_id') !== '' ? (int) $request->query('store_id') : null)
            : $this->currentStrStoreId();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = sprintf('%04d-%02d', $year, $m);
        }

        $storeFilter = $storeId ? "AND crm_store_id = {$storeId}" : '';

        // Collecté = sum of payments (effective_date in year)
        $collecteRows = DB::select("
            SELECT DATE_FORMAT(effective_date,'%Y-%m') as m, SUM(amount) as total
            FROM crm_payment_snapshots s1
            WHERE YEAR(effective_date) = ?
              AND payment_type_id = 1
              {$storeFilter}
              AND snapshot_date = (
                  SELECT MAX(s2.snapshot_date) FROM crm_payment_snapshots s2
                  WHERE s2.crm_payment_id = s1.crm_payment_id
              )
            GROUP BY m
        ", [$year]);
        $collecteByMonth = collect($collecteRows)->pluck('total', 'm')->map(fn($v) => (float) $v)->toArray();

        // Reste à payer = outstanding balance from collection rows (active receivables by due_date)
        $resteRows = DB::select("
            SELECT DATE_FORMAT(due_date,'%Y-%m') as m, SUM(rest_amount) as total
            FROM crm_collection_rows
            WHERE registration_status_id <> 10
              AND YEAR(due_date) = ?
              {$storeFilter}
            GROUP BY m
        ", [$year]);
        $resteByMonth = collect($resteRows)->pluck('total', 'm')->map(fn($v) => (float) $v)->toArray();

        // Encaissements = payments grouped by date_creation (cash recorded timestamp)
        $encRows = DB::select("
            SELECT DATE_FORMAT(date_creation,'%Y-%m') as m, SUM(amount) as total
            FROM crm_payment_snapshots s1
            WHERE YEAR(date_creation) = ?
              AND payment_type_id = 1
              {$storeFilter}
              AND snapshot_date = (
                  SELECT MAX(s2.snapshot_date) FROM crm_payment_snapshots s2
                  WHERE s2.crm_payment_id = s1.crm_payment_id
              )
            GROUP BY m
        ", [$year]);
        $encByMonth = collect($encRows)->pluck('total', 'm')->map(fn($v) => (float) $v)->toArray();

        // Dépenses from local warehouse
        $depRows = DB::table('site_expenses')
            ->where('crm_source', 'wimschool')
            ->whereYear('month', $year)
            ->when($storeId, function ($q) use ($storeId) {
                $q->whereIn('site_id', DB::table('sites')->where('crm_store_id', $storeId)->pluck('id'));
            })
            ->selectRaw("DATE_FORMAT(month,'%Y-%m') as m, SUM(amount) as total")
            ->groupBy('m')
            ->pluck('total', 'm')
            ->map(fn($v) => (float) $v)
            ->toArray();

        // Build 12-month arrays
        $seriesCA            = [];
        $seriesCollecte      = [];
        $seriesReste         = [];
        $seriesDepenses      = [];
        $seriesEncaissements = [];

        foreach ($months as $m) {
            $collecte = $collecteByMonth[$m] ?? 0;
            $reste    = $resteByMonth[$m]    ?? 0;
            $seriesCA[]            = round($collecte + $reste);
            $seriesCollecte[]      = round($collecte);
            $seriesReste[]         = round($reste);
            $seriesDepenses[]      = round($depRows[$m] ?? 0);
            $seriesEncaissements[] = round($encByMonth[$m] ?? 0);
        }

        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get(['id', 'name', 'crm_store_id']);

        return $this->view('backoffice.crm.stats-dashboard.ca-annuel', [
            'year'                => $year,
            'months'              => $months,
            'seriesCA'            => $seriesCA,
            'seriesCollecte'      => $seriesCollecte,
            'seriesReste'         => $seriesReste,
            'seriesDepenses'      => $seriesDepenses,
            'seriesEncaissements' => $seriesEncaissements,
            'sites'               => $sites,
            'storeId'             => $storeId,
        ]);
    }

    // =========================================================================
    // RÉSUMÉ ANNUEL — best center performance for primes (date range scan)
    // =========================================================================

    public function resumeAnnuel(Request $request): View
    {
        $sites        = Site::whereNotNull('crm_store_id')->orderBy('name')->get(['id', 'name', 'crm_store_id']);
        $snapshotDate = CrmPaymentSnapshot::max('snapshot_date');

        return $this->view('backoffice.crm.stats-dashboard.resume-annuel', [
            'sites'        => $sites,
            'snapshotDate' => $snapshotDate,
            'storeId'      => $this->currentStrStoreId(),
        ]);
    }

    /**
     * Scan the whole payment + collection + registration data over an arbitrary
     * date range and rank every center on three axes (encaissé, reste à payer,
     * inscriptions) plus a weighted composite "performance" score used to crown
     * the best center for primes.
     */
    public function resumeAnnuelData(Request $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->query('startDate');
        $endDate   = $request->query('endDate');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'startDate et endDate sont requis.'], 422);
        }

        try {
            Carbon::parse($startDate);
            Carbon::parse($endDate);
        } catch (\Throwable) {
            return response()->json(['error' => 'Dates invalides.'], 422);
        }

        // ── Encaissé per center (payments recorded in range) ─────────────────
        // date_creation = timestamp the cash was recorded (date_creation_date is
        // unpopulated in this DB). Dedup to the latest snapshot per payment id.
        $encRows = DB::select("
            SELECT crm_store_id, SUM(amount) as total, COUNT(*) as nb
            FROM crm_payment_snapshots s1
            WHERE date_creation BETWEEN ? AND ?
              AND payment_type_id = 1
              AND snapshot_date = (
                  SELECT MAX(s2.snapshot_date) FROM crm_payment_snapshots s2
                  WHERE s2.crm_payment_id = s1.crm_payment_id
              )
            GROUP BY crm_store_id
        ", [$startDate, $endDate]);
        $encaisse = [];
        foreach ($encRows as $r) {
            $encaisse[(int) $r->crm_store_id] = ['total' => (float) $r->total, 'nb' => (int) $r->nb];
        }

        // ── Reste à payer per center (active receivables due in range) ───────
        $resteRows = DB::select("
            SELECT crm_store_id,
                   SUM(rest_amount) as reste,
                   SUM(total_price) as ca,
                   COUNT(*) as cnt
            FROM crm_collection_rows
            WHERE due_date BETWEEN ? AND ?
              AND registration_status_id <> 10
            GROUP BY crm_store_id
        ", [$startDate, $endDate]);
        $reste = [];
        foreach ($resteRows as $r) {
            $reste[(int) $r->crm_store_id] = [
                'reste' => (float) $r->reste,
                'ca'    => (float) $r->ca,
                'cnt'   => (int) $r->cnt,
            ];
        }

        // ── Inscriptions per center (registrations created in range) ─────────
        $regRows = DB::table('crm_registrations')
            ->whereBetween('date_creation', [$startDate, $endDate])
            ->whereNotNull('date_creation')
            ->selectRaw('crm_store_id, COUNT(*) as cnt')
            ->groupBy('crm_store_id')
            ->get();
        $inscriptions = [];
        foreach ($regRows as $r) {
            $inscriptions[(int) $r->crm_store_id] = (int) $r->cnt;
        }

        $sites = Site::whereNotNull('crm_store_id')->pluck('name', 'crm_store_id');

        // Union of all store ids that have any data in the range
        $storeIds = collect(array_keys($encaisse))
            ->merge(array_keys($reste))
            ->merge(array_keys($inscriptions))
            ->unique()
            ->values();

        $rows = $storeIds->map(function ($sid) use ($encaisse, $reste, $inscriptions, $sites) {
            $enc       = $encaisse[$sid]['total'] ?? 0.0;
            $encNb     = $encaisse[$sid]['nb'] ?? 0;
            $resteVal  = $reste[$sid]['reste'] ?? 0.0;
            $ca        = $reste[$sid]['ca'] ?? 0.0;
            $insc      = $inscriptions[$sid] ?? 0;
            // Recovery rate = collected / (collected + outstanding). Higher = better
            // (encaissé beaucoup, peu de reste à payer).
            $base      = $enc + $resteVal;
            $recovery  = $base > 0 ? round($enc / $base * 100, 1) : null;

            return [
                'store_id'      => (int) $sid,
                'store_name'    => $sites[$sid] ?? 'Store #' . $sid,
                'encaisse'      => round($enc),
                'encaisse_nb'   => $encNb,
                'reste'         => round($resteVal),
                'ca'            => round($ca),
                'inscriptions'  => $insc,
                'recovery_rate' => $recovery,
            ];
        })->values();

        // ── Composite performance score (0–100) ──────────────────────────────
        // Normalise each metric across centers, then weight:
        //   encaissé 40% · recouvrement (recovery rate) 35% · inscriptions 25%.
        // "Most money recovered AND little reste à payer" => high recovery + high encaissé.
        $maxEnc  = max($rows->max('encaisse') ?: 1, 1);
        $maxInsc = max($rows->max('inscriptions') ?: 1, 1);

        $rows = $rows->map(function ($row) use ($maxEnc, $maxInsc) {
            $encScore  = $row['encaisse'] / $maxEnc * 100;
            $recScore  = $row['recovery_rate'] ?? 0; // already 0–100
            $inscScore = $row['inscriptions'] / $maxInsc * 100;
            $row['score'] = round($encScore * 0.40 + $recScore * 0.35 + $inscScore * 0.25, 1);
            return $row;
        })->values()->toArray();

        // Sorted views for the three category rankings
        $byEncaisse     = collect($rows)->sortByDesc('encaisse')->values()->toArray();
        $byInscriptions = collect($rows)->sortByDesc('inscriptions')->values()->toArray();
        // Best recouvrement = highest recovery rate (least reste relative to collected)
        $byRecouvrement = collect($rows)->sortByDesc(fn ($r) => $r['recovery_rate'] ?? -1)->values()->toArray();
        $byScore        = collect($rows)->sortByDesc('score')->values()->toArray();

        return response()->json([
            'rows'             => $rows,
            'by_encaisse'      => $byEncaisse,
            'by_inscriptions'  => $byInscriptions,
            'by_recouvrement'  => $byRecouvrement,
            'by_score'         => $byScore,
            'winner'           => $byScore[0] ?? null,
            'grand'            => [
                'encaisse'     => array_sum(array_column($rows, 'encaisse')),
                'reste'        => array_sum(array_column($rows, 'reste')),
                'inscriptions' => array_sum(array_column($rows, 'inscriptions')),
            ],
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'snapshot'         => CrmPaymentSnapshot::max('snapshot_date'),
        ]);
    }

    // =========================================================================
    // PERFORMANCE PROFESSEURS — top teachers, students at start vs lost
    // =========================================================================

    public function teacherPerformance(Request $request): View
    {
        $sites        = Site::whereNotNull('crm_store_id')->orderBy('name')->get(['id', 'name', 'crm_store_id']);
        $snapshotDate = CrmPaymentSnapshot::max('snapshot_date');

        return $this->view('backoffice.crm.stats-dashboard.teacher-performance', [
            'sites'        => $sites,
            'snapshotDate' => $snapshotDate,
            'storeId'      => $this->currentStrStoreId(),
        ]);
    }

    /**
     * Per-teacher evolution over an arbitrary date range. Reuses the same
     * payment-based début / ajout / quittant logic as crm:build-group-evolution,
     * but scoped live to the requested range and aggregated by teacher
     * (resolved from crm_classes.raw_data EMPLOYEE_TEACHER_FULL_NAME).
     *
     *   debuts    — students whose first paid month for the class is at/before
     *               the class start month (founding students)
     *   ajouts    — students whose first paid month is after class start
     *   quittants — registrations with status "Annulé" (definitive departures)
     *   actifs    — CLASS_COUNT_STUDENTS_ACTIVE summed across the teacher's classes
     */
    public function teacherPerformanceData(Request $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->query('startDate');
        $endDate   = $request->query('endDate');
        $storeId   = $request->query('strStoreId') ? (int) $request->query('strStoreId') : null;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'startDate et endDate sont requis.'], 422);
        }

        try {
            $startYm = Carbon::parse($startDate)->format('Y-m');
            $endYm   = Carbon::parse($endDate)->format('Y-m');
        } catch (\Throwable) {
            return response()->json(['error' => 'Dates invalides.'], 422);
        }

        $cacheKey = 'crm.teacher_perf:' . ($storeId ?: 'all') . ":{$startDate}:{$endDate}";

        $payload = Cache::remember($cacheKey, 1800, function () use ($storeId, $startDate, $endDate, $startYm, $endYm) {
            return $this->computeTeacherPerformance($storeId, $startDate, $endDate, $startYm, $endYm);
        });

        return response()->json($payload + ['snapshot' => CrmPaymentSnapshot::max('snapshot_date')]);
    }

    private function computeTeacherPerformance(?int $storeId, string $startDate, string $endDate, string $startYm, string $endYm): array
    {
        // Classes in formation/préparation, optionally scoped to a center.
        $classes = \App\Models\CrmClass::query()
            ->whereNotNull('class_id')
            ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.STATUS_NAME')) IN ('En formation', 'En Préparation')")
            ->get();

        if ($classes->isEmpty()) {
            return ['teachers' => [], 'totals' => ['debuts' => 0, 'ajouts' => 0, 'quittants' => 0, 'actifs' => 0, 'teachers' => 0, 'classes' => 0], 'start_date' => $startDate, 'end_date' => $endDate];
        }

        // Map raw_data.ID (= registration crm_class_id) → teacher + class meta.
        $classMeta = []; // [rawId => ['teacher_id','teacher_name','site_id','start_ym','actifs','name']]
        $rawIds    = [];
        foreach ($classes as $class) {
            $raw   = $class->raw_data ?? [];
            $rawId = isset($raw['ID']) ? (int) $raw['ID'] : null;
            if ($rawId === null) {
                continue;
            }
            $rawIds[] = $rawId;
            $classMeta[$rawId] = [
                'teacher_id'   => isset($raw['EMPLOYEE_TEACHER_ID']) ? (int) $raw['EMPLOYEE_TEACHER_ID'] : 0,
                'teacher_name' => trim($raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? '') ?: 'Non assigné',
                'site_id'      => (int) ($class->site_id ?? 0),
                'start_ym'     => isset($raw['START_DATE']) ? Carbon::parse($raw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m') : null,
                'actifs'       => (int) ($raw['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0),
                'name'         => $class->name ?? "#{$rawId}",
            ];
        }

        // Registrations for those classes (only the classes we kept).
        $registrations = \App\Models\CrmRegistration::query()
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->whereIn('crm_class_id', $rawIds)
            ->get(['crm_student_id', 'crm_class_id', 'status', 'raw_data']);

        // First non-inscription paid month per student, within the range.
        $studentIds = $registrations->pluck('crm_student_id')->unique()->values()->all();
        $payMonthsByStudent = collect();
        if (!empty($studentIds)) {
            $payMonthsByStudent = DB::table('crm_payment_snapshots')
                ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
                ->whereIn('student_id', $studentIds)
                ->whereBetween('effective_date', [$startDate, $endDate])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.ITEMS_NAME')) NOT LIKE '%inscription%'")
                ->selectRaw('student_id, DATE_FORMAT(effective_date, "%Y-%m") as pay_month')
                ->distinct()
                ->get()
                ->groupBy('student_id')
                ->map(fn ($r) => $r->pluck('pay_month')->sort()->values()->all());
        }

        // Aggregate per teacher.
        $teachers = []; // [teacher_id => bucket]
        foreach ($registrations as $reg) {
            $cid  = (int) $reg->crm_class_id;
            $meta = $classMeta[$cid] ?? null;
            if (!$meta) {
                continue;
            }
            $tid = $meta['teacher_id'];
            if (!isset($teachers[$tid])) {
                $teachers[$tid] = [
                    'teacher_id'   => $tid,
                    'teacher_name' => $meta['teacher_name'],
                    'debuts'       => 0,
                    'ajouts'       => 0,
                    'quittants'    => 0,
                    'classes'      => [],
                ];
            }
            $teachers[$tid]['classes'][$cid] = true;

            $sid    = (int) $reg->crm_student_id;
            $status = $reg->status;
            $raw    = is_array($reg->raw_data) ? $reg->raw_data : json_decode($reg->raw_data, true);

            // Quittant = définitive departure (status Annulé) within the range.
            if ($status === 'Annulé') {
                $teachers[$tid]['quittants']++;
            }

            $classYm = $meta['start_ym'];
            if (!$classYm) {
                continue;
            }
            $regYm  = isset($raw['START_DATE']) ? Carbon::parse($raw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m') : $classYm;
            $months = $payMonthsByStudent->get($sid, []);
            $firstForClass = collect($months)->first(fn ($m) => $m >= $regYm);

            if (!$firstForClass) {
                if ($status === 'Active' && $regYm <= $classYm && $classYm >= $startYm && $classYm <= $endYm) {
                    $teachers[$tid]['debuts']++;
                }
                continue;
            }
            if ($firstForClass <= $classYm) {
                $teachers[$tid]['debuts']++;
            } else {
                $teachers[$tid]['ajouts']++;
            }
        }

        // Sum actifs per teacher across their classes.
        $actifsByTeacher = [];
        foreach ($classMeta as $meta) {
            $actifsByTeacher[$meta['teacher_id']] = ($actifsByTeacher[$meta['teacher_id']] ?? 0) + $meta['actifs'];
        }

        $result = collect($teachers)->map(function ($t) use ($actifsByTeacher) {
            $debuts    = $t['debuts'];
            $ajouts    = $t['ajouts'];
            $quittants = $t['quittants'];
            $actifs    = $actifsByTeacher[$t['teacher_id']] ?? 0;
            $entrants  = $debuts + $ajouts; // total students brought in
            // Retention = kept / entered. Higher = fewer losses relative to intake.
            $retention = $entrants > 0 ? round(max(0, $entrants - $quittants) / $entrants * 100, 1) : null;

            return [
                'teacher_id'   => $t['teacher_id'],
                'teacher_name' => $t['teacher_name'],
                'debuts'       => $debuts,
                'ajouts'       => $ajouts,
                'quittants'    => $quittants,
                'actifs'       => $actifs,
                'classes'      => count($t['classes']),
                'retention'    => $retention,
            ];
        })->values()->sortByDesc('actifs')->values()->toArray();

        return [
            'teachers'   => $result,
            'totals'     => [
                'debuts'    => array_sum(array_column($result, 'debuts')),
                'ajouts'    => array_sum(array_column($result, 'ajouts')),
                'quittants' => array_sum(array_column($result, 'quittants')),
                'actifs'    => array_sum(array_column($result, 'actifs')),
                'teachers'  => count($result),
                'classes'   => count($classMeta),
            ],
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ];
    }

    public function refresh(Request $request): RedirectResponse
    {
        // Bust stats cache only — never run sync commands inside HTTP requests
        // (they take 30–120s and cause PHP execution timeouts on shared hosting)
        Cache::flush();

        return redirect()
            ->route('backoffice.crm.statistiques', $request->query())
            ->with('success', 'Cache vidé. Les données seront actualisées lors du prochain sync automatique (toutes les 2h). Pour forcer: php artisan crm:sync-all');
    }

    // -------------------------------------------------------------------------

    private function encaissementByCenter(int $months, ?int $storeId): array
    {
        $from   = Carbon::today()->subMonths($months)->startOfMonth()->toDateString();
        $latest = CrmPaymentSnapshot::max('snapshot_date');

        if (!$latest) return [];

        // Use latest snapshot_date per payment_id to deduplicate across historical
        // snapshots — this lets the 6/12-month views read older snapshot rows that
        // the current rolling-2-month snapshot no longer contains.
        $storeFilter = $storeId ? "AND crm_store_id = {$storeId}" : '';
        $rows = CrmPaymentSnapshot::query()
            ->fromRaw("(
                SELECT crm_store_id, date_creation_date, amount
                FROM crm_payment_snapshots s1
                WHERE date_creation_date >= ?
                  AND payment_type_id = 1
                  {$storeFilter}
                  AND snapshot_date = (
                      SELECT MAX(s2.snapshot_date)
                      FROM crm_payment_snapshots s2
                      WHERE s2.crm_payment_id = s1.crm_payment_id
                  )
            ) AS deduped", [$from])
            ->selectRaw("crm_store_id, DATE_FORMAT(date_creation_date,'%Y-%m') as month, SUM(amount) as total")
            ->groupBy('crm_store_id', 'month')
            ->orderBy('month')
            ->get();

        // pivot: [storeId => [month => total]]
        $pivot  = [];
        $allMonths = [];
        foreach ($rows as $r) {
            $pivot[$r->crm_store_id][$r->month] = (float) $r->total;
            $allMonths[$r->month] = true;
        }
        ksort($allMonths);

        $sites  = Site::whereNotNull('crm_store_id')
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->pluck('name', 'crm_store_id');

        return [
            'pivot'     => $pivot,
            'months'    => array_keys($allMonths),
            'sites'     => $sites->toArray(),
            'snapshot'  => $latest,
        ];
    }

    private function recouvrementByCenter(?int $storeId): array
    {
        $rows = CrmCollectionRow::query()
            ->where('registration_status_name', 'Active')
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->selectRaw('crm_store_id, store_name,
                SUM(rest_amount) as total_due,
                SUM(CASE WHEN due_date < CURDATE() THEN rest_amount ELSE 0 END) as overdue,
                SUM(CASE WHEN due_date >= CURDATE() THEN rest_amount ELSE 0 END) as upcoming,
                COUNT(*) as dossiers')
            ->groupBy('crm_store_id', 'store_name')
            ->orderByDesc('total_due')
            ->get();

        return $rows->map(fn ($r) => [
            'store_id'   => $r->crm_store_id,
            'store_name' => $r->store_name ?? 'Store #' . $r->crm_store_id,
            'total_due'  => (float) $r->total_due,
            'overdue'    => (float) $r->overdue,
            'upcoming'   => (float) $r->upcoming,
            'dossiers'   => (int) $r->dossiers,
        ])->toArray();
    }

    private function registrationsByCenter(int $months, ?int $storeId): array
    {
        // Uses normalized date_creation column (indexed) instead of JSON_EXTRACT in WHERE.
        // Populated by crm:backfill-columns (one-time) + crm:sync-registrations (ongoing).
        $from = Carbon::today('Africa/Casablanca')->subMonths($months)->startOfMonth()->toDateString();

        $rows = CrmRegistration::query()
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->where('date_creation', '>=', $from)
            ->whereNotNull('date_creation')
            ->selectRaw("crm_store_id,
                DATE_FORMAT(date_creation, '%Y-%m') as month,
                COUNT(*) as cnt")
            ->groupBy('crm_store_id', 'month')
            ->orderBy('month')
            ->get();

        $pivot     = [];
        $allMonths = [];
        foreach ($rows as $r) {
            $pivot[$r->crm_store_id][$r->month] = (int) $r->cnt;
            $allMonths[$r->month] = true;
        }
        ksort($allMonths);

        $sites = Site::whereNotNull('crm_store_id')
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->pluck('name', 'crm_store_id');

        return [
            'pivot'  => $pivot,
            'months' => array_keys($allMonths),
            'sites'  => $sites->toArray(),
        ];
    }

    private function periodComparison(?int $storeId): array
    {
        $latest = CrmPaymentSnapshot::max('snapshot_date');
        if (!$latest) return [];

        $tz = 'Africa/Casablanca';

        $thisMonthStart = Carbon::today($tz)->startOfMonth()->toDateString();
        $thisMonthEnd   = Carbon::today($tz)->toDateString();
        $lastMonthStart = Carbon::today($tz)->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd   = Carbon::today($tz)->subMonth()->endOfMonth()->toDateString();
        $prevYearStart  = Carbon::today($tz)->subYear()->startOfMonth()->toDateString();
        $prevYearEnd    = Carbon::today($tz)->subYear()->endOfMonth()->toDateString();

        $storeFilter = $storeId ? "AND crm_store_id = {$storeId}" : '';
        // Deduplicate by taking latest snapshot per payment so historical months
        // (prev_year) are read from older snapshots, not just the rolling-2-month one.
        $sum = function (string $from, string $to) use ($storeFilter): float {
            return (float) \Illuminate\Support\Facades\DB::selectOne("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM crm_payment_snapshots s1
                WHERE date_creation_date BETWEEN ? AND ?
                  AND payment_type_id = 1
                  {$storeFilter}
                  AND snapshot_date = (
                      SELECT MAX(s2.snapshot_date)
                      FROM crm_payment_snapshots s2
                      WHERE s2.crm_payment_id = s1.crm_payment_id
                  )
            ", [$from, $to])->total;
        };

        // Uses normalized date_creation column (indexed) instead of JSON_EXTRACT.
        // Populated by crm:backfill-columns + crm:sync-registrations (ongoing).
        $countReg = fn ($from, $to) => CrmRegistration::query()
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->whereBetween('date_creation', [$from, $to])
            ->whereNotNull('date_creation')
            ->count();

        return [
            'this_month'  => ['encaissement' => $sum($thisMonthStart, $thisMonthEnd),  'inscriptions' => $countReg($thisMonthStart, $thisMonthEnd),  'label' => 'Ce mois'],
            'last_month'  => ['encaissement' => $sum($lastMonthStart, $lastMonthEnd),  'inscriptions' => $countReg($lastMonthStart, $lastMonthEnd),  'label' => 'Mois dernier'],
            'prev_year'   => ['encaissement' => $sum($prevYearStart,  $prevYearEnd),   'inscriptions' => $countReg($prevYearStart,  $prevYearEnd),   'label' => Carbon::today()->subYear()->format('M Y')],
        ];
    }
}
