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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'months'    => $months,
            'storeId'   => $storeId,
        ]));
    }

    // -------------------------------------------------------------------------

    private function encaissementByCenter(int $months, ?int $storeId): array
    {
        $from   = Carbon::today()->subMonths($months)->startOfMonth();
        $latest = CrmPaymentSnapshot::max('snapshot_date');

        if (!$latest) return [];

        $rows = CrmPaymentSnapshot::query()
            ->where('snapshot_date', $latest)
            ->where('effective_date', '>=', $from)
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->selectRaw("crm_store_id, DATE_FORMAT(effective_date,'%Y-%m') as month, SUM(amount) as total")
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
        $from = Carbon::today()->subMonths($months)->startOfMonth()->toDateString();

        $rows = CrmRegistration::query()
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->whereRaw("DATE(JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.REGISTRATION_DATE'))) >= ?", [$from])
            ->selectRaw("crm_store_id,
                DATE_FORMAT(JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.REGISTRATION_DATE')),'%Y-%m') as month,
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

        $thisMonthStart = Carbon::today()->startOfMonth()->toDateString();
        $thisMonthEnd   = Carbon::today()->toDateString();
        $lastMonthStart = Carbon::today()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd   = Carbon::today()->subMonth()->endOfMonth()->toDateString();
        $prevYearStart  = Carbon::today()->subYear()->startOfMonth()->toDateString();
        $prevYearEnd    = Carbon::today()->subYear()->endOfMonth()->toDateString();

        $sum = fn ($from, $to) => (float) CrmPaymentSnapshot::where('snapshot_date', $latest)
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->whereBetween('effective_date', [$from, $to])
            ->sum('amount');

        $countReg = fn ($from, $to) => CrmRegistration::query()
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->whereRaw("DATE(JSON_UNQUOTE(JSON_EXTRACT(raw_data,'$.REGISTRATION_DATE'))) BETWEEN ? AND ?", [$from, $to])
            ->count();

        return [
            'this_month'  => ['encaissement' => $sum($thisMonthStart, $thisMonthEnd),  'inscriptions' => $countReg($thisMonthStart, $thisMonthEnd),  'label' => 'Ce mois'],
            'last_month'  => ['encaissement' => $sum($lastMonthStart, $lastMonthEnd),  'inscriptions' => $countReg($lastMonthStart, $lastMonthEnd),  'label' => 'Mois dernier'],
            'prev_year'   => ['encaissement' => $sum($prevYearStart,  $prevYearEnd),   'inscriptions' => $countReg($prevYearStart,  $prevYearEnd),   'label' => Carbon::today()->subYear()->format('M Y')],
        ];
    }
}
