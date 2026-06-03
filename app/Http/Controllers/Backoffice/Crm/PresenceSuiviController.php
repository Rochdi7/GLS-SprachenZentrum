<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use App\Services\Crm\Stats\PresenceSuiviService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PresenceSuiviController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
        protected PresenceSuiviService $service,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    public function index(Request $request): View
    {
        $storeId    = $this->currentStrStoreId();
        $yearMonth  = $request->query('month', Carbon::today('Africa/Casablanca')->format('Y-m'));
        $bustCache  = $request->boolean('refresh');

        if ($bustCache) {
            Cache::forget("crm.presence_suivi.{$storeId}.{$yearMonth}");
        }

        $data         = $this->service->buildMonth($storeId, $yearMonth);
        $globalFraud  = $this->service->globalFraud($yearMonth);
        $totals       = $this->service->allTimeTotals($storeId);
        $employeeStats = $this->service->employeeStats($storeId);

        $prevMonth = Carbon::parse($yearMonth . '-01')->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($yearMonth . '-01')->addMonth()->format('Y-m');

        return $this->view('backoffice.crm.presence-suivi.index', array_merge($data, [
            'storeId'     => $storeId,
            'yearMonth'   => $yearMonth,
            'prevMonth'   => $prevMonth,
            'nextMonth'   => $nextMonth,
            'monthLabel'  => Carbon::parse($yearMonth . '-01')->translatedFormat('F Y'),
            'globalFraud'  => $globalFraud,
            'totals'       => $totals,
            'teacherStats' => $employeeStats,
        ]));
    }

    public function details(Request $request): JsonResponse
    {
        $storeId = $this->currentStrStoreId();
        $status  = in_array($request->query('status'), ['saisie', 'draft']) ? $request->query('status') : 'saisie';

        $groups = $this->service->groupDetails($storeId, $status);

        return response()->json(['groups' => $groups]);
    }
}
