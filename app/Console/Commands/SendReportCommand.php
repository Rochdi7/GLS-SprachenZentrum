<?php

namespace App\Console\Commands;

use App\Mail\Reports\MonthlyProfPaymentReportMail;
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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Unified report dispatcher. Generates data and sends the appropriate email.
 *
 * Usage (automatic, via scheduler):
 *   php artisan reports:send weekly-presence
 *   php artisan reports:send weekly-prof-payment
 *   php artisan reports:send weekly-unpaid-students
 *   php artisan reports:send weekly-group-performance
 *   php artisan reports:send weekly-center-performance
 *
 * Usage (manual with custom period, max 31 days):
 *   php artisan reports:send weekly-presence --from=2026-06-01 --to=2026-06-07
 *
 * Monthly report (requires --year and --month):
 *   php artisan reports:send monthly-revenue --year=2026 --month=5
 *   php artisan reports:send monthly-prof-payment --year=2026 --month=5
 */
class SendReportCommand extends Command
{
    protected $signature = 'reports:send
        {type : Report type: weekly-presence | weekly-prof-payment | weekly-unpaid-students | weekly-group-performance | weekly-center-performance | monthly-revenue | monthly-prof-payment}
        {--from=   : Custom start date (Y-m-d). Weekly only.}
        {--to=     : Custom end date (Y-m-d). Weekly only.}
        {--year=   : Year for monthly report.}
        {--month=  : Month (1-12) for monthly report.}
        {--dry-run : Build report data but do not send email.}';

    protected $description = 'Generate and send a GLS report email';

    public function handle(): int
    {
        $type = $this->argument('type');

        try {
            [$data, $mailable] = $this->buildReport($type);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Report generation failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('[dry-run] Report built — no email sent.');
            $this->line('  Period : ' . ($data['period_label'] ?? $data['month_label'] ?? '—'));
            return self::SUCCESS;
        }

        $recipients = $this->resolveRecipients();

        foreach ($recipients as $email) {
            Mail::to($email)->send(new $mailable($data));
            $this->line("  → Sent to {$email}");
        }

        $this->info("Report [{$type}] sent to " . count($recipients) . ' recipient(s).');

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------

    private function buildReport(string $type): array
    {
        $resolver = ReportPeriodResolver::make();

        return match ($type) {
            'weekly-presence' => [
                app(WeeklyPresenceReportService::class)->generate(...$this->weeklyPeriod($resolver)),
                WeeklyPresenceReportMail::class,
            ],
            'weekly-prof-payment' => [
                app(WeeklyProfPaymentReportService::class)->generate(...$this->weeklyPeriod($resolver)),
                WeeklyProfPaymentReportMail::class,
            ],
            'weekly-unpaid-students' => [
                app(WeeklyUnpaidStudentsReportService::class)->generate(...$this->weeklyPeriod($resolver)),
                WeeklyUnpaidStudentsReportMail::class,
            ],
            'weekly-group-performance' => [
                app(WeeklyGroupPerformanceReportService::class)->generate(...$this->weeklyPeriod($resolver)),
                WeeklyGroupPerformanceReportMail::class,
            ],
            'weekly-center-performance' => [
                app(WeeklyCenterPerformanceReportService::class)->generate(...$this->weeklyPeriod($resolver)),
                WeeklyCenterPerformanceReportMail::class,
            ],
            'monthly-revenue' => [
                app(MonthlyRevenueReportService::class)->generate(...$this->monthlyPeriod($resolver)),
                MonthlyRevenueReportMail::class,
            ],
            'monthly-prof-payment' => [
                app(WeeklyProfPaymentReportService::class)->generate(...$this->monthlyPeriod($resolver)),
                MonthlyProfPaymentReportMail::class,
            ],
            default => throw new \InvalidArgumentException("Unknown report type: [{$type}]"),
        };
    }

    /**
     * Returns [Carbon $from, Carbon $to] for weekly reports.
     * Uses custom --from/--to if provided; falls back to currentWeek().
     */
    private function weeklyPeriod(ReportPeriodResolver $resolver): array
    {
        $from = $this->option('from');
        $to   = $this->option('to');

        if ($from && $to) {
            $period = $resolver->custom($from, $to);
        } else {
            $period = $resolver->currentWeek();
        }

        $this->info('Period: ' . $resolver->label($period['from'], $period['to']));

        return [$period['from'], $period['to']];
    }

    /**
     * Returns [Carbon $from, Carbon $to] for the monthly revenue report.
     * Requires --year and --month, or defaults to last completed month.
     */
    private function monthlyPeriod(ReportPeriodResolver $resolver): array
    {
        $year  = $this->option('year');
        $month = $this->option('month');

        if ($year && $month) {
            $period = $resolver->singleMonth((int) $year, (int) $month);
        } else {
            $period = $resolver->lastMonth();
        }

        $this->info('Period: ' . $resolver->label($period['from'], $period['to']));

        return [$period['from'], $period['to']];
    }

    /**
     * In test mode → only test email. Otherwise all configured recipients.
     */
    private function resolveRecipients(): array
    {
        if (config('reports.test_mode', true)) {
            return [config('reports.test_email')];
        }

        $recipients = config('reports.recipients', []);

        return count($recipients) > 0
            ? $recipients
            : [config('reports.test_email')];
    }
}
