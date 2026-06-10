<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmCollectionRow;
use App\Models\CrmPaymentSnapshot;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use App\Services\Crm\Stats\CollectionsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Collections dashboard — KPIs, aging buckets, top debtors, upcoming dues.
 *
 * Extends BaseCrmController so it inherits the center-context helpers
 * (scopedCrm, view, currentStrStoreId, etc.) without duplicating them.
 */
class CollectionsController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
        protected CollectionsService $collections,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    /**
     * Main collections dashboard.
     */
    public function index(Request $request): View
    {
        $strStoreId = $this->currentStrStoreId();
        $error      = null;

        $kpis            = [];
        $topDebtors      = [];
        $upcomingDues    = [];
        $agingBuckets    = [];
        $perfByCenter    = [];
        $recoveryByCenter = [];

        try {
            // Scope the service to the current center's token
            $scopedCrm   = $this->scopedCrm();
            $service     = new CollectionsService($scopedCrm);

            $kpis             = $service->kpis($strStoreId);
            $topDebtors       = $service->topDebtors($strStoreId);
            $upcomingDues     = $service->upcomingDues($strStoreId);
            $agingBuckets     = $service->agingBuckets($strStoreId);
            $perfByCenter     = $this->collections->performanceByCenter();
            $recoveryByCenter = $this->collections->recoveryByCenter($strStoreId);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $snapshotDate = CrmPaymentSnapshot::max('snapshot_date');

        return $this->view('backoffice.crm.collections.index', compact(
            'kpis',
            'topDebtors',
            'upcomingDues',
            'agingBuckets',
            'perfByCenter',
            'recoveryByCenter',
            'strStoreId',
            'error',
            'snapshotDate',
        ));
    }

    /**
     * AJAX drill-down: return students for a given KPI bucket.
     * ?type=outstanding|dueToday|dueWeek|dueMonth|overdue7|overdue30|overdue60|overdue90|aging_0|aging_8|aging_31|aging_61|aging_90
     */
    public function drill(Request $request): JsonResponse
    {
        $type       = $request->query('type', 'outstanding');
        $strStoreId = $this->currentStrStoreId();
        $today      = Carbon::today();

        $query = CrmCollectionRow::query()
            ->where('registration_status_name', 'Active')
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId));

        match ($type) {
            'dueToday'  => $query->whereDate('due_date', $today->toDateString()),
            'dueWeek'   => $query->whereBetween('due_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()]),
            'dueMonth'  => $query->whereBetween('due_date', [$today->toDateString(), $today->copy()->endOfMonth()->toDateString()]),
            'overdue7'  => $query->where('due_date', '<=', $today->copy()->subDays(7)->toDateString()),
            'overdue30' => $query->where('due_date', '<=', $today->copy()->subDays(30)->toDateString()),
            'overdue60' => $query->where('due_date', '<=', $today->copy()->subDays(60)->toDateString()),
            'overdue90' => $query->where('due_date', '<=', $today->copy()->subDays(90)->toDateString()),
            'aging_0'   => $query->whereBetween('due_date', [$today->copy()->subDays(7)->toDateString(),  $today->toDateString()]),
            'aging_8'   => $query->whereBetween('due_date', [$today->copy()->subDays(30)->toDateString(), $today->copy()->subDays(8)->toDateString()]),
            'aging_31'  => $query->whereBetween('due_date', [$today->copy()->subDays(60)->toDateString(), $today->copy()->subDays(31)->toDateString()]),
            'aging_61'  => $query->whereBetween('due_date', [$today->copy()->subDays(90)->toDateString(), $today->copy()->subDays(61)->toDateString()]),
            'aging_90'  => $query->where('due_date', '<', $today->copy()->subDays(90)->toDateString()),
            default     => null, // outstanding = no extra filter
        };

        $rows = $query->orderByDesc('rest_amount')
            ->get(['student_id', 'student_name', 'store_name', 'rest_amount', 'due_date', 'registration_id'])
            ->map(fn ($r) => [
                'student_id'      => $r->student_id,
                'student_name'    => $r->student_name ?? '—',
                'store_name'      => $r->store_name   ?? '—',
                'registration_id' => $r->registration_id,
                'amount'          => (float) $r->rest_amount,
                'due_date'        => $r->due_date?->toDateString(),
                'overdue_days'    => $r->due_date && $r->due_date->lt($today)
                    ? $r->due_date->diffInDays($today)
                    : 0,
            ]);

        return response()->json(['rows' => $rows, 'total' => $rows->sum('amount'), 'count' => $rows->count()]);
    }

    /**
     * AJAX: CA par mois grouped by center.
     * CA = SUM(total_price) where registration_status_id != 10 and due_date in range.
     * ?startDate=YYYY-MM-DD&endDate=YYYY-MM-DD&strStoreId=N&groupBy=month|day
     */
    public function caData(Request $request): JsonResponse
    {
        $startDate = $request->query('startDate') ?: Carbon::today()->startOfMonth()->toDateString();
        $endDate   = $request->query('endDate')   ?: Carbon::today()->toDateString();
        $storeId   = $request->query('strStoreId') ? (int) $request->query('strStoreId') : $this->currentStrStoreId();
        $groupBy   = $request->query('groupBy', 'month'); // month | day

        $dateFmt = $groupBy === 'day'
            ? "DATE_FORMAT(due_date,'%Y-%m-%d')"
            : "DATE_FORMAT(due_date,'%Y-%m')";

        $rows = CrmCollectionRow::query()
            ->where('registration_status_id', '!=', 10)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->selectRaw("crm_store_id, store_name, {$dateFmt} AS period, SUM(total_price) AS ca, COUNT(*) AS nb")
            ->groupBy('crm_store_id', 'store_name', 'period')
            ->orderBy('period')
            ->get();

        $periods  = $rows->pluck('period')->unique()->sort()->values()->toArray();
        $stores   = $rows->groupBy('crm_store_id');
        $datasets = [];

        foreach ($stores as $sid => $sRows) {
            $pivot = $sRows->pluck('ca', 'period')->map(fn ($v) => (float) $v)->toArray();
            $datasets[] = [
                'store_id'   => (int) $sid,
                'store_name' => $sRows->first()->store_name ?? 'Store #' . $sid,
                'data'       => array_map(fn ($p) => round($pivot[$p] ?? 0, 2), $periods),
                'total'      => (float) $sRows->sum('ca'),
                'nb'         => (int) $sRows->sum('nb'),
            ];
        }

        usort($datasets, fn ($a, $b) => $b['total'] <=> $a['total']);

        return response()->json([
            'periods'     => $periods,
            'datasets'    => $datasets,
            'grand_total' => array_sum(array_column($datasets, 'total')),
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'group_by'    => $groupBy,
        ]);
    }

    /**
     * Bust all collections cache keys and redirect back to the dashboard.
     */
    public function refresh(Request $request): RedirectResponse
    {
        $strStoreId = $this->currentStrStoreId();
        $suffix     = $strStoreId ?: 'all';

        Cache::forget('crm.collections.kpis:' . $suffix);
        Cache::forget('crm.collections.top_debtors:' . $suffix . ':20');
        Cache::forget('crm.collections.upcoming:' . $suffix . ':14');
        Cache::forget('crm.collections.aging:' . $suffix);
        Cache::forget('crm.collections.perf_by_center');
        Cache::forget('crm.collections.recovery_by_center:' . $suffix);

        return redirect()
            ->route('backoffice.crm.collections.index', array_filter(['strStoreId' => $strStoreId]))
            ->with('success', 'Données de recouvrement actualisées.');
    }
}
