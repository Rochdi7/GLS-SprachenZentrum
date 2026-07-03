<?php

namespace App\Http\Controllers\Backoffice\Reports;

use App\Http\Controllers\Controller;
use App\Mail\Reports\MonthlyProfPaymentReportMail;
use App\Mail\Reports\MonthlyRevenueReportMail;
use App\Mail\Reports\WeeklyCenterPerformanceReportMail;
use App\Mail\Reports\WeeklyGroupPerformanceReportMail;
use App\Mail\Reports\WeeklyPresenceReportMail;
use App\Mail\Reports\WeeklyProfPaymentReportMail;
use App\Mail\Reports\WeeklyUnpaidStudentsReportMail;
use App\Models\ReportSendLog;
use App\Services\Reports\Monthly\MonthlyRevenueReportService;
use App\Services\Reports\ReportPeriodResolver;
use App\Services\Reports\Weekly\WeeklyCenterPerformanceReportService;
use App\Services\Reports\Weekly\WeeklyGroupPerformanceReportService;
use App\Services\Reports\Weekly\WeeklyPresenceReportService;
use App\Services\Reports\Weekly\WeeklyProfPaymentReportService;
use App\Services\Reports\Weekly\WeeklyUnpaidStudentsReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ScheduledReportsController extends Controller
{
    public function index(): View
    {
        $logs = ReportSendLog::with('sender')
            ->latest()
            ->limit(50)
            ->get();

        return view('backoffice.reports.scheduled.index', [
            'autoSendEnabled' => (bool) config('reports.auto_send_enabled', false),
            'testMode'        => (bool) config('reports.test_mode', true),
            'testEmail'       => config('reports.test_email'),
            'timezone'        => config('reports.timezone', 'Africa/Casablanca'),
            'maxDays'         => config('reports.max_period_days', 31),
            'logs'            => $logs,
        ]);
    }

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

        $this->dispatch($mailClass, $data, 'weekly', $validated['type'], $period['from'], $period['to']);

        return back()->with('success', "Rapport [{$type}] envoyé pour la période {$resolver->label($period['from'], $period['to'])}.");
    }

    public function sendMonthly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'  => 'required|in:monthly-revenue,monthly-prof-payment',
            'year'  => 'required|integer|min:2020|max:' . (now()->year + 1),
            'month' => 'required|integer|min:1|max:12',
        ]);

        $resolver = ReportPeriodResolver::make();
        $period   = $resolver->singleMonth((int) $validated['year'], (int) $validated['month']);

        [$data, $mailClass] = $this->buildMonthly($validated['type'], $period['from'], $period['to']);

        $this->dispatch($mailClass, $data, 'monthly', $validated['type'], $period['from'], $period['to']);

        return back()->with('success', "Rapport [" . ReportSendLog::typeLabel($validated['type']) . "] envoyé pour " . ($data['month_label'] ?? $resolver->label($period['from'], $period['to'])) . '.');
    }

    /**
     * Resend a previously logged report using the same type + period.
     */
    public function resend(ReportSendLog $log): RedirectResponse
    {
        if ($log->category === 'monthly') {
            [$data, $mailClass] = $this->buildMonthly($log->type, $log->period_from, $log->period_to);
            $this->dispatch($mailClass, $data, 'monthly', $log->type, $log->period_from, $log->period_to);
        } else {
            [$data, $mailClass] = $this->buildWeekly($log->type, $log->period_from, $log->period_to);
            $this->dispatch($mailClass, $data, 'weekly', $log->type, $log->period_from, $log->period_to);
        }

        return back()->with('success', "Rapport [" . ReportSendLog::typeLabel($log->type) . "] renvoyé.");
    }

    // -------------------------------------------------------------------------

    private function buildWeekly(string $type, $from, $to): array
    {
        return match ($type) {
            'weekly-presence'           => [app(WeeklyPresenceReportService::class)->generate($from, $to),          WeeklyPresenceReportMail::class],
            'weekly-prof-payment'       => [app(WeeklyProfPaymentReportService::class)->generate($from, $to),       WeeklyProfPaymentReportMail::class],
            'weekly-unpaid-students'    => [app(WeeklyUnpaidStudentsReportService::class)->generate($from, $to),    WeeklyUnpaidStudentsReportMail::class],
            'weekly-group-performance'  => [app(WeeklyGroupPerformanceReportService::class)->generate($from, $to),  WeeklyGroupPerformanceReportMail::class],
            'weekly-center-performance' => [app(WeeklyCenterPerformanceReportService::class)->generate($from, $to), WeeklyCenterPerformanceReportMail::class],
        };
    }

    private function buildMonthly(string $type, $from, $to): array
    {
        return match ($type) {
            'monthly-revenue'      => [app(MonthlyRevenueReportService::class)->generate($from, $to),      MonthlyRevenueReportMail::class],
            'monthly-prof-payment' => [app(WeeklyProfPaymentReportService::class)->generate($from, $to),    MonthlyProfPaymentReportMail::class],
        };
    }

    private function dispatch(string $mailClass, array $data, string $category, string $type, $from, $to): void
    {
        $recipients = config('reports.test_mode', true)
            ? [config('reports.test_email')]
            : (config('reports.recipients') ?: [config('reports.test_email')]);

        $status = 'success';
        $error  = null;

        try {
            foreach ($recipients as $email) {
                Mail::to($email)->send(new $mailClass($data));
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $error  = $e->getMessage();
        }

        ReportSendLog::create([
            'type'        => $type,
            'category'    => $category,
            'period_from' => $from instanceof \Carbon\Carbon ? $from->toDateString() : $from,
            'period_to'   => $to instanceof \Carbon\Carbon   ? $to->toDateString()   : $to,
            'recipients'  => $recipients,
            'status'      => $status,
            'error'       => $error,
            'sent_by'     => Auth::id(),
        ]);

        if ($status === 'failed') {
            throw new \RuntimeException("Échec envoi rapport: {$error}");
        }
    }
}
