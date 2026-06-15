<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Jobs\Crm\SendDailyReportJob;
use App\Models\CrmDailyReport;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmWeeklyReport;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use App\Services\Crm\Stats\DailyReportService;
use App\Services\Crm\Stats\WeeklyReportService;
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
        protected WeeklyReportService $weeklyService,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    /**
     * List CEO reports — daily and weekly, with date filtering.
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'daily');

        // Date filter
        $fromDate = $request->filled('from') ? $request->input('from') : null;
        $toDate   = $request->filled('to')   ? $request->input('to')   : null;

        $dailyQuery = CrmDailyReport::latest()->limit(60);
        if ($fromDate) {
            $dailyQuery->where('report_date', '>=', $fromDate);
        }
        if ($toDate) {
            $dailyQuery->where('report_date', '<=', $toDate);
        }
        $reports = $dailyQuery->get();

        $weeklyQuery = CrmWeeklyReport::latest()->limit(20);
        if ($fromDate) {
            $weeklyQuery->where('week_start', '>=', $fromDate);
        }
        if ($toDate) {
            $weeklyQuery->where('week_end', '<=', $toDate);
        }
        $weeklyReports = $weeklyQuery->get();

        return $this->view('backoffice.crm.reports.index', compact('reports', 'weeklyReports', 'tab', 'fromDate', 'toDate'));
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

    public function resend(string $date): RedirectResponse
    {
        $report = CrmDailyReport::where('report_date', $date)->firstOrFail();

        SendDailyReportJob::dispatch($report);

        return redirect()
            ->route('backoffice.crm.reports.show', ['date' => $date])
            ->with('success', 'Rapport renvoyé par email pour le ' . $report->report_date->format('d/m/Y') . '.');
    }

    /**
     * Manually generate (or regenerate) the weekly report for the week containing $date.
     */
    public function generateWeekly(Request $request): RedirectResponse
    {
        $date = $request->input('date') ?: null;

        try {
            $report = $this->weeklyService->generate($date);
        } catch (\Throwable $e) {
            return redirect()
                ->route('backoffice.crm.reports.index', ['tab' => 'weekly'])
                ->with('error', 'Erreur : ' . $e->getMessage());
        }

        return redirect()
            ->route('backoffice.crm.reports.index', ['tab' => 'weekly'])
            ->with('success', 'Rapport hebdomadaire généré : ' . $report->week_label . '.');
    }
}
