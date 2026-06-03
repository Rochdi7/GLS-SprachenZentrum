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
        // ── CRM SYNC: every 2 hours (local data warehouse refresh) ──────────
        // Fires at: 00:00, 02:00, 04:00, 06:00, 08:00, 10:00, 12:00,
        //           14:00, 16:00, 18:00, 20:00, 22:00 (Casablanca time)
        //
        // withoutOverlapping(120): mutex held for up to 2 hours — prevents
        // double-run if a sync takes longer than 2h on a slow shared server.
        //
        // runInBackground(): scheduler process returns immediately; the sync
        // runs in a detached process (safe on shared hosting).
        $schedule->command('crm:sync-all')
            ->cron('0 */2 * * *')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(120)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/crm-sync-all.log'));

        // ── CEO Daily Report ─────────────────────────────────────────────────
        // Runs after the 06:00 sync finishes (~06:15), gives fresh data at 07:00
        $schedule->command('crm:daily-report')
            ->dailyAt('07:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-daily-report.log'));

        // ── Non-CRM jobs ─────────────────────────────────────────────────────
        $schedule->command('gls:generate-level-followups')
            ->dailyAt('00:15')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/level-followups-schedule.log'));

        // Homeschool attendance sync: monthly on the 1st (non-CRM domain)
        $schedule->command('homeschool:sync-attendance')
            ->monthlyOn(1, '02:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/homeschool-sync.log'));
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
