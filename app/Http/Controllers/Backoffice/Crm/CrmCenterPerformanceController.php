<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\Stats\CenterPerformanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmCenterPerformanceController extends BaseCrmController
{
    public function index(Request $request, CenterPerformanceService $service): View
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $schoolYearId = $request->query('schoolYearId') ? (int)$request->query('schoolYearId') : null;
        $bustCache = $request->boolean('refresh');

        $data = $service->buildDashboardData($startDate, $endDate, $schoolYearId, $bustCache);

        return $this->view('backoffice.crm.center-performance', [
            'dashboard' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'schoolYearId' => $schoolYearId,
        ]);
    }
}
