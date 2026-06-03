<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmPaymentSnapshot;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use App\Services\Crm\Stats\CollectionsService;
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

        try {
            // Scope the service to the current center's token
            $scopedCrm   = $this->scopedCrm();
            $service     = new CollectionsService($scopedCrm);

            $kpis         = $service->kpis($strStoreId);
            $topDebtors   = $service->topDebtors($strStoreId);
            $upcomingDues = $service->upcomingDues($strStoreId);
            $agingBuckets = $service->agingBuckets($strStoreId);
            $perfByCenter = $this->collections->performanceByCenter(); // local DB, no scoping needed
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
            'strStoreId',
            'error',
            'snapshotDate',
        ));
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

        return redirect()
            ->route('backoffice.crm.collections.index', array_filter(['strStoreId' => $strStoreId]))
            ->with('success', 'Données de recouvrement actualisées.');
    }
}
