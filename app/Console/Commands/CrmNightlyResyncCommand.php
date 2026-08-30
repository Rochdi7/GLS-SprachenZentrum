<?php

namespace App\Console\Commands;

use App\Models\CrmResyncLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Nightly deep re-sync at 00:00 Casablanca time.
 *
 * Covers a longer history window than the 2-hourly crm:sync-all so that
 * any data modified in Wimschool during the day (absences entered by
 * reception, payment corrections, inscription updates) is reflected before
 * the next business day.
 *
 * Writes a row to crm_resync_log with user_id = null (system-triggered).
 * The scheduler lock (crm.resync.lock) is force-cleared at the start so a
 * stuck web-UI lock never blocks the nightly run.
 */
class CrmNightlyResyncCommand extends Command
{
    protected $signature = 'crm:nightly-resync
        {--dry-run : Show what would run without executing}';

    protected $description = 'Nightly deep re-sync of all CRM data domains (runs once at 00:00 Casablanca)';

    // Single nightly window — Wimschool rate-limits during business hours, so
    // this is now the ONLY scheduled job that hits their API. Delays are
    // deliberately generous and page caps high enough to complete a full sweep
    // in one run; every step upserts on a unique key, so a step that overlaps
    // data already present updates rows in place rather than duplicating them.
    private const STEPS = [
        'homeschool:mirror-core'         => ['--months' => 3],
        'crm:sync-attendance'             => ['--months' => 3, '--max-pages' => 900, '--delay' => 1500, '--size' => 200],
        'crm:sync-collections'            => ['--all' => true, '--delay' => 1000],
        'crm:snapshot-payments'           => ['--months' => 3, '--pause' => 60],
        'crm:sync-registrations'          => ['--all' => true, '--delay' => 1000],
        'crm:sync-expenses'               => ['--all' => true, '--months' => 3, '--delay' => 1000],
        'crm:sync-payment-allocations'    => ['--all' => true, '--months' => 6, '--delay' => 1500],
        'crm:build-presence-summary'      => ['--all' => true, '--months' => 6],
        'crm:build-group-evolution'       => ['--all' => true],
        'crm:churn-scores'                => [],
    ];

    // Cache keys flushed after sync so dashboards read fresh data on next load
    private const FLUSH_PATTERNS = [
        'crm.presence_suivi.',
        'crm.collections.',
        'crm.stats.',
        'crm.group_evolution.',
        'crm.insights.',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║  CRM NIGHTLY RESYNC — ' . Carbon::now('Africa/Casablanca')->format('Y-m-d H:i') . '    ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');

        if ($this->option('dry-run')) {
            foreach (self::STEPS as $cmd => $opts) {
                $this->line("  [DRY] php artisan {$cmd} " . $this->optsToString($opts));
            }
            return self::SUCCESS;
        }

        // Force-clear any stuck web-UI lock before starting
        Cache::forget('crm.resync.lock');

        $results   = [];
        $wallStart = microtime(true);

        foreach (self::STEPS as $cmd => $opts) {
            $t0 = microtime(true);
            $this->info("▶ {$cmd}");

            try {
                $this->call($cmd, $opts);
                $elapsed   = round(microtime(true) - $t0, 1);
                $results[] = ['step' => $cmd, 'status' => 'ok',    'elapsed' => $elapsed . 's'];
                $this->info("  ✓ done in {$elapsed}s");
            } catch (\Throwable $e) {
                $elapsed   = round(microtime(true) - $t0, 1);
                $results[] = ['step' => $cmd, 'status' => 'error', 'elapsed' => $elapsed . 's', 'error' => $e->getMessage()];
                $this->error("  ✗ FAILED: " . $e->getMessage());
                Log::error("crm:nightly-resync step={$cmd} failed", ['error' => $e->getMessage()]);
                // Continue — one failed step must not block the rest
            }

            $this->line('');
        }

        // Flush all dashboard caches — next page load re-queries from rebuilt DB
        Cache::flush();

        $duration  = (int) round(microtime(true) - $wallStart);
        $hasErrors = collect($results)->contains('status', 'error');

        // Write audit row with user_id = null (system / scheduler)
        CrmResyncLog::create([
            'user_id'          => null,
            'domain'           => 'nightly',
            'domain_label'     => 'Sync nocturne automatique (00h)',
            'status'           => $hasErrors ? 'partial' : 'ok',
            'crm_store_id'     => null,
            'steps'            => $results,
            'duration_seconds' => $duration,
        ]);

        $this->info('');
        if ($hasErrors) {
            $this->warn("Nightly resync finished with errors in {$duration}s. Check logs.");
        } else {
            $this->info("✓ Nightly resync complete in {$duration}s.");
        }

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }

    private function optsToString(array $opts): string
    {
        return collect($opts)->map(fn ($v, $k) => $v === true ? $k : "{$k}={$v}")->implode(' ');
    }
}
