<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmDailyReport;
use App\Models\CrmPaymentSnapshot;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use App\Services\Crm\Stats\DailyReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyReportController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
        protected DailyReportService $reportService,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    /**
     * List the last 30 CEO reports.
     */
    public function index(): View
    {
        $reports = CrmDailyReport::latest()->limit(30)->get();

        return $this->view('backoffice.crm.reports.index', compact('reports'));
    }

    /**
     * Show a single report by date (yyyy-mm-dd).
     */
    public function show(string $date): View
    {
        $report       = CrmDailyReport::where('report_date', $date)->firstOrFail();
        $snapshotDate = CrmPaymentSnapshot::max('snapshot_date');

        return $this->view('backoffice.crm.reports.show', compact('report', 'snapshotDate'));
    }

    /**
     * Generate a fresh report (for yesterday or today) and redirect.
     */
    public function generate(Request $request): RedirectResponse
    {
        $date = $request->input('date') ?: null;

        try {
            $data   = $this->reportService->generate($date);
            $report = $this->reportService->store($data);
        } catch (\Throwable $e) {
            return redirect()
                ->route('backoffice.crm.reports.index')
                ->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }

        return redirect()
            ->route('backoffice.crm.reports.show', ['date' => $report->report_date->toDateString()])
            ->with('success', 'Rapport généré pour le ' . $report->report_date->format('d/m/Y') . '.');
    }
}
