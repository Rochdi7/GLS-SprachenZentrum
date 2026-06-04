<?php

namespace App\Console\Commands;

use App\Models\CrmPaymentSnapshot;
use App\Models\Site;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ONE-TIME full historical backfill from 2025-01-01 to today.
 *
 * After this runs, the daily crm:sync-all only needs --months=2 or --months=1
 * because all history is already local.
 *
 * Steps (in dependency order):
 *   1. registrations   — homeschool:mirror-core (no date filter, fetches all)
 *   2. attendance      — crm:sync-attendance --months=18 (2025-01 → now)
 *   3. collections     — crm:sync-collections (no date filter, fetches all)
 *   4. allocations     — crm:sync-payment-allocations --months=18
 *   5. payments_snap   — loop monthly from 2025-01 to today via crm:snapshot-payments
 *
 * Payment snapshots are done month-by-month so each snapshot date window
 * is small (30 days) and won't OOM the process.
 *
 * Usage:
 *   php artisan crm:backfill               (all steps, all stores, 2025-01-01 → today)
 *   php artisan crm:backfill --from=allocations   (resume from a specific step)
 *   php artisan crm:backfill --only=payments_snap (run one step only)
 *   php artisan crm:backfill --dry-run     (show what would run)
 */
class CrmBackfillCommand extends Command
{
    protected $signature = 'crm:backfill
        {--from=       : Resume from this step name (skips earlier steps)}
        {--only=       : Run only this step}
        {--since=2025-01-01 : Start date for backfill (default 2025-01-01)}
        {--dry-run     : Show steps without executing}';

    protected $description = 'One-time full historical CRM backfill from 2025-01-01 to today.';

    private const STEPS = [
        'registrations' => 'Mirror all registrations (classes + students + registrations)',
        'attendance'    => 'Backfill session attendance (bulk, 18 months)',
        'collections'   => 'Backfill payment-collection receivables (all)',
        'allocations'   => 'Backfill payment allocations (bulk, 18 months)',
        'payments_snap' => 'Backfill payment snapshots month-by-month (2025-01 → today)',
    ];

    public function handle(Crm $crm): int
    {
        $since   = $this->option('since') ?: '2025-01-01';
        $only    = $this->option('only');
        $from    = $this->option('from');
        $dryRun  = (bool) $this->option('dry-run');
        $months  = (int) Carbon::parse($since)->diffInMonths(Carbon::today()) + 1;

        $this->info("CRM FULL BACKFILL — {$since} → " . Carbon::today()->toDateString());
        $this->info("Computed months window: {$months}");
        $this->newLine();

        $skipping = !empty($from);

        foreach (self::STEPS as $step => $description) {
            // --only: run exactly one step
            if ($only && $step !== $only) continue;

            // --from: fast-forward to named step
            if ($skipping) {
                if ($step === $from) {
                    $skipping = false;
                } else {
                    $this->line("  <fg=gray>[SKIP]  {$step}</>");
                    continue;
                }
            }

            if ($dryRun) {
                $this->line("  [DRY]  {$step}: {$description}");
                continue;
            }

            $this->info("┌─ [{$step}] {$description}");
            $started = microtime(true);

            $exit = match ($step) {
                'registrations' => $this->backfillRegistrations(),
                'attendance'    => $this->call('crm:sync-attendance', [
                    '--all'    => true,
                    '--months' => $months,
                    '--delay'  => 600,
                ]),
                'collections'   => $this->call('crm:sync-collections', [
                    '--all'   => true,
                    '--delay' => 400,
                ]),
                'allocations'   => $this->call('crm:sync-payment-allocations', [
                    '--all'    => true,
                    '--months' => $months,
                    '--delay'  => 1000,
                ]),
                'payments_snap' => $this->backfillPaymentSnapshots($since, $crm),
                default         => self::FAILURE,
            };

            $elapsed = round(microtime(true) - $started, 1);

            if ($exit === self::SUCCESS || $exit === 0) {
                $this->info("└─ [OK]  {$step} done in {$elapsed}s");
            } else {
                $this->error("└─ [FAIL] {$step} failed after {$elapsed}s — continuing...");
            }

            $this->newLine();
        }

        $this->info('BACKFILL COMPLETE.');
        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------

    private function backfillRegistrations(): int
    {
        // mirror-core has no date range — it fetches all registrations/classes/students
        return $this->call('homeschool:mirror-core', ['--full' => true]);
    }

    /**
     * Loop month by month from $since to today, running crm:snapshot-payments
     * for each month-end date. Each run fetches the 30-day window ending on
     * that date, so we get full payment history without one massive request.
     *
     * Skips months that already have snapshot rows to make the command resumable.
     */
    private function backfillPaymentSnapshots(string $since, Crm $crm): int
    {
        $start   = Carbon::parse($since)->startOfMonth();
        $end     = Carbon::today();
        $current = $start->copy();
        $failed  = 0;

        // Build set of snapshot_dates already in DB so we can skip them
        $existing = CrmPaymentSnapshot::selectRaw('snapshot_date')
            ->groupBy('snapshot_date')
            ->pluck('snapshot_date')
            ->map(fn ($d) => (string) $d)
            ->flip()
            ->toArray();

        while ($current->lte($end)) {
            // Use last day of month, capped at today
            $snapDate = $current->copy()->endOfMonth()->min($end)->toDateString();

            if (isset($existing[$snapDate])) {
                $this->line("  [SKIP] snapshot {$snapDate} already exists");
                $current->addMonth();
                continue;
            }

            $this->line("  → snapshot for {$snapDate}");

            $exit = $this->call('crm:snapshot-payments', [
                '--date'  => $snapDate,
                '--pause' => 90,   // 90s between centers during backfill — avoids 429
            ]);

            if ($exit !== self::SUCCESS) {
                $this->error("  [FAIL] snapshot {$snapDate} — waiting 3min before next month...");
                $failed++;
                sleep(180); // long wait after a failed month — API needs to recover
            } else {
                // Pause between successful months
                if ($current->copy()->addMonth()->lte($end)) {
                    $this->line('  (pause 60s between months...)');
                    sleep(60);
                }
            }

            $current->addMonth();
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
