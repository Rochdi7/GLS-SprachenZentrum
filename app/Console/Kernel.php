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
     * All CRM API sync runs ONCE per night via crm:nightly-resync at 00:00
     * Casablanca — Wimschool rate-limits requests during business hours.
     * Do not add daytime jobs that call their API.
     * See: docs/crm-warehouse-architecture.md
     */
    protected function schedule(Schedule $schedule): void
    {
        // ── CRM API sync — ONCE PER NIGHT ────────────────────────────────────
        // Wimschool rate-limits us during the day, so all API-hitting syncs are
        // collapsed into a single overnight window starting at 00:00 Casablanca.
        // crm:sync-all is NOT scheduled: crm:nightly-resync covers the same
        // domains over a longer history window. Run it by hand if ever needed.

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

        // ── Weekly CEO report — every Friday at 06:00 Casablanca ────────────
        // Covers Mon–Sun of the just-ended week (anchor = Thursday = last day in week).
        $schedule->command('crm:weekly-report')
            ->weeklyOn(5, '06:00')  // 5 = Friday
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/crm-weekly-report.log'));

        // ── Step 3 — :30 — Level followups ───────────────────────────────────
        $schedule->command('gls:generate-level-followups')
            ->dailyAt('06:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/level-followups-schedule.log'));

        // ── Step 4 — :40 — Wimschool attendance ─────────────────────────────
        $schedule->command('wimschool:sync-attendance')
            ->dailyAt('03:30')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(120)
            ->appendOutputTo(storage_path('logs/wimschool-sync.log'));

        // ── Step 5 — :45 — Stats dashboard self-heal ────────────────────────
        // Runs after sync-all (:00) finishes. Two jobs:
        //   1. Guarded backfill: only touches crm_payment_snapshots when the
        //      normalized date_creation_date column still has NULLs — so on a
        //      healthy DB it does nothing (no 135k-row scan). This is what was
        //      breaking "Classement encaissement par période" in production.
        //   2. cache:clear so the stats dashboard (10-min cache) never serves
        //      numbers older than the last sync.
        $schedule->call(function () {
            $nulls = \Illuminate\Support\Facades\DB::table('crm_payment_snapshots')
                ->whereNull('date_creation_date')
                ->whereNotNull('raw_data')
                ->exists();

            if ($nulls) {
                \Illuminate\Support\Facades\Artisan::call('crm:backfill-columns');
            }

            \Illuminate\Support\Facades\Artisan::call('cache:clear');
        })
            ->name('stats-dashboard-self-heal')
            ->dailyAt('04:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/stats-self-heal.log'));

        // ── Hikvision device sync — every 15 minutes ─────────────────────────
        // Pulls device/persons/attendance/alarms from the local access-control
        // terminal. Previously unscheduled (manual-only via CLI), so device
        // data never refreshed on its own. See HIKVISION_SETUP.md.
        $schedule->command('hikvision:sync')
            ->everyFifteenMinutes()
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/hikvision-sync.log'));

        // ── Sitemap — regenerate nightly so new blog posts / studienkollegs
        //    appear without a manual step. Writes public/sitemap.xml.
        $schedule->command('sitemap:generate')
            ->dailyAt('03:00')
            ->timezone('Africa/Casablanca')
            ->appendOutputTo(storage_path('logs/sitemap.log'));

        // ── Deep resync every 2h — pulls 3 months of history so that any data
        //    modified in Wimschool during the day (absences entered by reception,
        //    payment corrections, inscription updates) is reflected well before
        //    the next business day. Runs at :50 so it starts after sync-all finishes.
        //    Force-clears stuck web-UI locks before each run.
        $schedule->command('crm:nightly-resync')
            ->dailyAt('00:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(360)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/crm-nightly-resync.log'));

        // ── Repair orphan payment→registration links — 05:00, after every
        //    nightly sync has finished writing. Pure local-DB work (no API):
        //    backfills registration_id on snapshots from the allocations
        //    mirror, so Vue 360 attaches each payment to its true inscription
        //    instead of guessing. See CrmResolvePaymentLinksCommand.
        $schedule->command('crm:resolve-payment-links')
            ->dailyAt('05:00')
            ->timezone('Africa/Casablanca')
            ->withoutOverlapping(60)
            ->appendOutputTo(storage_path('logs/crm-resolve-payment-links.log'));

        // ── Weekly reports — every Friday at midnight (Casablanca) ───────────
        // Controlled by REPORTS_AUTO_SEND_ENABLED=true in .env
        $weeklyDay = config('reports.weekly_send_day', 5);   // 5 = Friday
        $weeklyTime = config('reports.weekly_send_time', '00:00');
        $tz = config('reports.timezone', 'Africa/Casablanca');

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

        // ── Monthly reports — 1st of each month (Casablanca) ─────────────────
        // Controlled by REPORTS_AUTO_SEND_ENABLED=true in .env (same flag as weekly)
        $monthlyDay = config('reports.monthly_send_day', 1);
        $monthlyTime = config('reports.monthly_send_time', '00:00');

        $monthlyReports = [
            'monthly-revenue',
            'monthly-prof-payment',
        ];

        foreach ($monthlyReports as $type) {
            $schedule->command("reports:send {$type}")
                ->monthlyOn($monthlyDay, $monthlyTime)
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
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
