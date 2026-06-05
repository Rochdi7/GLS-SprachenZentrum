<?php

namespace App\Http\Controllers\Backoffice\Reports;

use App\Http\Controllers\Controller;
use App\Mail\Reports\MonthlyRevenueReportMail;
use App\Mail\Reports\WeeklyCenterPerformanceReportMail;
use App\Mail\Reports\WeeklyGroupPerformanceReportMail;
use App\Mail\Reports\WeeklyPresenceReportMail;
use App\Mail\Reports\WeeklyProfPaymentReportMail;
use App\Mail\Reports\WeeklyUnpaidStudentsReportMail;
use App\Services\Reports\Monthly\MonthlyRevenueReportService;
use App\Services\Reports\ReportPeriodResolver;
use App\Services\Reports\Weekly\WeeklyCenterPerformanceReportService;
use App\Services\Reports\Weekly\WeeklyGroupPerformanceReportService;
use App\Services\Reports\Weekly\WeeklyPresenceReportService;
use App\Services\Reports\Weekly\WeeklyProfPaymentReportService;
use App\Services\Reports\Weekly\WeeklyUnpaidStudentsReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ScheduledReportsController extends Controller
{
    public function index(): View
    {
        return view('backoffice.reports.scheduled.index', [
            'autoSendEnabled' => (bool) config('reports.auto_send_enabled', false),
            'testMode'        => (bool) config('reports.test_mode', true),
            'testEmail'       => config('reports.test_email'),
            'timezone'        => config('reports.timezone', 'Africa/Casablanca'),
            'maxDays'         => config('reports.max_period_days', 31),
        ]);
    }

    /**
     * Manually trigger a weekly report for a custom period.
     * Validates that the range does not exceed max_period_days.
     */
    public function sendWeekly(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $resolver = ReportPeriodResolver::make();

        $validated = $request->validate([
            'type' => 'required|in:weekly-presence,weekly-prof-payment,weekly-unpaid-students,weekly-group-performance,weekly-center-performance',
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        try {
            $period = $resolver->custom($validated['from'], $validated['to']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['period' => $e->getMessage()])->withInput();
        }

        [$data, $mailClass] = $this->buildWeekly($type, $period['from'], $period['to']);

        $this->dispatch($mailClass, $data);

        return back()->with('success', "Rapport [{$type}] envoyé pour la période {$resolver->label($period['from'], $period['to'])}.");
    }

    /**
     * Manually trigger the monthly revenue report for a specific month.
     */
    public function sendMonthly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year'  => 'required|integer|min:2020|max:' . (now()->year + 1),
            'month' => 'required|integer|min:1|max:12',
        ]);

        $resolver = ReportPeriodResolver::make();
        $period   = $resolver->singleMonth((int) $validated['year'], (int) $validated['month']);

        $service = app(MonthlyRevenueReportService::class);
        $data    = $service->generate($period['from'], $period['to']);

        $this->dispatch(MonthlyRevenueReportMail::class, $data);

        return back()->with('success', "Rapport mensuel envoyé pour {$data['month_label']}.");
    }

    // -------------------------------------------------------------------------

    private function buildWeekly(string $type, $from, $to): array
    {
        return match ($type) {
            'weekly-presence'          => [app(WeeklyPresenceReportService::class)->generate($from, $to),          WeeklyPresenceReportMail::class],
            'weekly-prof-payment'      => [app(WeeklyProfPaymentReportService::class)->generate($from, $to),       WeeklyProfPaymentReportMail::class],
            'weekly-unpaid-students'   => [app(WeeklyUnpaidStudentsReportService::class)->generate($from, $to),    WeeklyUnpaidStudentsReportMail::class],
            'weekly-group-performance' => [app(WeeklyGroupPerformanceReportService::class)->generate($from, $to),  WeeklyGroupPerformanceReportMail::class],
            'weekly-center-performance'=> [app(WeeklyCenterPerformanceReportService::class)->generate($from, $to), WeeklyCenterPerformanceReportMail::class],
        };
    }

    private function dispatch(string $mailClass, array $data): void
    {
        $recipients = config('reports.test_mode', true)
            ? [config('reports.test_email')]
            : (config('reports.recipients') ?: [config('reports.test_email')]);

        foreach ($recipients as $email) {
            Mail::to($email)->send(new $mailClass($data));
        }
    }
}
