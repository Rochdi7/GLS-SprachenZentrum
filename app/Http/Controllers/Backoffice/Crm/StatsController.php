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
        $storeId = $request->filled('store_id') ? (int) $request->query('store_id') : $this->currentStrStoreId();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = sprintf('%04d-%02d', $year, $m);
        }

        $storeFilter = $storeId ? "AND crm_store_id = {$storeId}" : '';

        // CA = total_price from collections WHERE status != 10, grouped by due_date month
        $caRows = DB::select("
            SELECT DATE_FORMAT(due_date,'%Y-%m') as m, SUM(total_price) as total
            FROM crm_collection_rows
            WHERE registration_status_id <> 10
              AND YEAR(due_date) = ?
              {$storeFilter}
            GROUP BY m
        ", [$year]);
        $caByMonth = collect($caRows)->pluck('total', 'm')->map(fn($v) => (float) $v)->toArray();

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
            $ca       = $caByMonth[$m]  ?? 0;
            $collecte = $collecteByMonth[$m] ?? 0;
            $seriesCA[]            = round($ca);
            $seriesCollecte[]      = round($collecte);
            $seriesReste[]         = round(max(0, $ca - $collecte));
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
