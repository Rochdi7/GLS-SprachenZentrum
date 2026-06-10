<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * HOSTINGER CRON SETUP (set once, never touch again):
     *   * * * * *  /usr/local/bin/php /home/USERNAME/domains/DOMAIN/artisan schedule:run >> /dev/null 2>&1
     *
     * All CRM sync is now orchestrated by crm:sync-all running every 2 hours.
     * Individual CRM commands are no longer scheduled directly.
     * See: docs/crm-warehouse-architecture.md
     */
    protected function schedule(Schedule $schedule): void
    {
        // ── Step 1 — :00 — CRM full sync ─────────────────────────────────────
        $schedule->command('crm:sync-all')
            ->cron('0 */2 * * *')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(120)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/crm-sync-all.log'));

        // ── Expenses full backfill — 1st of each month at 04:00 ─────────────
        // Pulls all expenses from Sept 2025 to ensure retroactive CRM entries
        // (expenses entered weeks after their date) are captured.
        $schedule->command('crm:sync-expenses --all --from=2025-09-01')
            ->monthlyOn(1, '04:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(120)
            ->appendOutputTo(storage_path('logs/crm-sync-expenses.log'));

        // ── Monthly re-snapshot — 5th of each month at 03:00 ────────────────
        // Re-fetches last 3 months to catch retroactive CRM entries (payments
        // entered weeks after their effective_date — common in Wimschool CRM).
        // Runs last day of month — fetches 4 months back to catch retroactive entries
        // (payments entered weeks after their effective_date — common in Wimschool CRM)
        $schedule->command('crm:snapshot-payments --months=4')
            ->monthlyOn(28, '03:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(240)
            ->appendOutputTo(storage_path('logs/crm-snapshot-monthly.log'));

        // ── Step 2 — :20 — Daily report (after sync finishes) ────────────────
        $schedule->command('crm:daily-report')
            ->cron('20 */2 * * *')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-daily-report.log'));

        // ── Step 3 — :30 — Level followups ───────────────────────────────────
        $schedule->command('gls:generate-level-followups')
            ->cron('30 */2 * * *')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/level-followups-schedule.log'));

        // ── Step 4 — :40 — Wimschool attendance ─────────────────────────────
        $schedule->command('wimschool:sync-attendance')
            ->cron('40 */2 * * *')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/wimschool-sync.log'));

        // ── Deep resync every 2h — pulls 3 months of history so that any data
        //    modified in Wimschool during the day (absences entered by reception,
        //    payment corrections, inscription updates) is reflected well before
        //    the next business day. Runs at :50 so it starts after sync-all finishes.
        //    Force-clears stuck web-UI locks before each run.
        $schedule->command('crm:nightly-resync')
            ->cron('50 */2 * * *')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(110)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/crm-nightly-resync.log'));

        // ── Weekly reports — every Friday at midnight (Casablanca) ───────────
        // Controlled by REPORTS_AUTO_SEND_ENABLED=true in .env
        $weeklyDay  = config('reports.weekly_send_day', 5);   // 5 = Friday
        $weeklyTime = config('reports.weekly_send_time', '00:00');
        $tz         = config('reports.timezone', 'Africa/Casablanca');

        $weeklyReports = [
            'weekly-presence',
            'weekly-prof-payment',
            'weekly-unpaid-students',
            'weekly-group-performance',
            'weekly-center-performance',
        ];

        foreach ($weeklyReports as $type) {
            $schedule->command("reports:send {$type}")
                ->weeklyOn($weeklyDay, $weeklyTime)
                ->timezone($tz)
                ->withoutOverlapping()
                ->when(fn () => (bool) config('reports.auto_send_enabled', false))
                ->appendOutputTo(storage_path("logs/report-{$type}.log"));
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
