<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Suivi niveau: génération quotidienne (idempotent)
        $schedule->command('gls:generate-level-followups')
            ->dailyAt('00:15')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/level-followups-schedule.log'));

        // ✅ CRM payment snapshot: capture quotidienne pour audit + détection fraude
        $schedule->command('crm:snapshot-payments')
            ->dailyAt('01:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-snapshot-payments.log'));

        // ✅ Homeschool attendance sync: monthly on the 1st at 2 AM
        $schedule->command('homeschool:sync-attendance')
            ->monthlyOn(1, '02:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/homeschool-sync.log'));

        // ✅ CRM collections sync: nightly fetch of receivables/installments
        $schedule->command('crm:sync-collections --all --delay=400')
            ->dailyAt('02:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-sync-collections.log'));

        // ✅ CRM registrations sync: nightly slow fetch, feeds churn scoring
        $schedule->command('crm:sync-registrations --all --delay=600')
            ->dailyAt('02:30')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-sync-registrations.log'));

        // ✅ CRM churn scores: daily recompute for all stores
        $schedule->command('crm:churn-scores --all')
            ->dailyAt('03:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-churn-scores.log'));

        // ✅ Daily CEO Report: generate every morning at 08:00 Casablanca time
        $schedule->command('crm:daily-report')
            ->dailyAt('08:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-daily-report.log'));
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
