<?php

namespace App\Console\Commands;

use App\Models\CrmSyncLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Master orchestrator for the CRM local data warehouse sync.
 *
 * Runs all sync and aggregate-build steps in the correct dependency order.
 * Designed for Hostinger shared hosting — no Redis, no Supervisor, no queues.
 * Triggered by Laravel Scheduler every 2 hours via a single Hostinger cron entry.
 *
 * STEP ORDER (dependency chain):
 *   1. classes      — mirror classes/students/registrations (FK source for attendance)
 *   2. attendance   — requires classes to exist (crm_class_id FK)
 *   3. collections  — independent, feeds quittant detection
 *   4. payments     — independent fraud snapshot
 *   5. allocations  — NEW: mirrors payment-allocations (feeds group evolution)
 *   6. presence     — requires attendance to be synced
 *   7. evolution    — requires allocations + classes + collections
 *   8. report       — requires snapshots to be current
 *
 * LOCKING:
 *   Cache::put(LOCK_KEY) prevents two simultaneous runs on shared hosting.
 *   withoutOverlapping(120) in Kernel.php adds a second layer of protection.
 *
 * RESUME:
 *   --resume  : skips steps already marked 'done' today in crm_sync_log
 *   --from=X  : jumps to step X regardless of sync log
 *   --dry-run : shows what would run without executing
 *
 * FAILURE RECOVERY:
 *   Each step failure is logged but does not abort the run.
 *   Steps after a failed step continue (attendance failure ≠ skip collections).
 *   On next run, use --resume or --from=failed_step.
 *
 * Hostinger cron entry (set once):
 *   * * * * *  php /path/to/artisan schedule:run >> /dev/null 2>&1
 *
 * Kernel.php schedule:
 *   $schedule->command('crm:sync-all')->cron('0 * /2 * * *')->withoutOverlapping(120);
 */
class CrmSyncAllCommand extends Command
{
    protected $signature = 'crm:sync-all
        {--resume   : Skip steps already completed today}
        {--from=    : Resume from a specific step name}
        {--dry-run  : Show steps without executing}
        {--force    : Skip overlap lock check}';

    protected $description = 'Full CRM sync: mirror API data → build aggregates → warm caches (runs every 2h via scheduler)';

    private const LOCK_KEY = 'crm.sync-all.lock';
    private const LOCK_TTL = 7200; // 2 hours

    /**
     * Sync steps in dependency order.
     * Format: 'step_key' => ['artisan:command', '--option1 --option2=val', 'Human description']
     *
     * The option string is parsed into an array by callStep() — see that method.
     */
    private const STEPS = [
        'classes'          => [
            'homeschool:mirror-core',
            '--months=2',
            'Mirror classes, students, registrations from Homeschool API',
        ],
        'attendance'       => [
            'crm:sync-attendance',
            '--all --months=3 --delay=600',
            'Sync session presence — bulk endpoint 500/page (was 25/page)',
        ],
        'collections'      => [
            'crm:sync-collections',
            '--all --delay=500',
            'Sync payment-collection receivables',
        ],
        'payments_snap'    => [
            'crm:snapshot-payments',
            '',
            'Daily payment snapshot for fraud detection',
        ],
        'allocations'      => [
            'crm:sync-payment-allocations',
            '--all --months=6 --delay=1000',
            'Mirror payment allocations — NEW, eliminates live API on group-evolution',
        ],
        'presence_summary' => [
            'crm:build-presence-summary',
            '--all --months=3',
            'Aggregate monthly attendance — replaces PHP CarbonPeriod loops',
        ],
        'group_evolution'  => [
            'crm:build-group-evolution',
            '--all --months=6',
            'Precompute group evolution snapshot — zero API calls on dashboard',
        ],
        'daily_report'     => [
            'crm:daily-report',
            '',
            'Generate CEO daily report',
        ],
    ];

    public function handle(): int
    {
        if (!$this->option('force') && Cache::has(self::LOCK_KEY)) {
            $this->warn('[BLOCKED] crm:sync-all is already running.');
            $this->warn('Use --force to override the lock (only if previous run crashed).');
            return self::FAILURE;
        }

        Cache::put(self::LOCK_KEY, now()->toIso8601String(), self::LOCK_TTL);

        $this->printHeader();

        try {
            return $this->runSteps();
        } finally {
            Cache::forget(self::LOCK_KEY);
        }
    }

    private function runSteps(): int
    {
        $resumeFrom    = $this->option('from');
        $resumeMode    = (bool) $this->option('resume');
        $skipping      = !empty($resumeFrom);
        $overallFailed = false;

        foreach (self::STEPS as $stepName => [$command, $options, $description]) {

            // --from=stepName: fast-forward to the named step
            if ($skipping) {
                if ($stepName === $resumeFrom) {
                    $skipping = false;
                } else {
                    $this->line("  <fg=gray>[SKIP]  {$stepName}</>");
                    continue;
                }
            }

            // --resume: skip steps already successfully completed today
            if ($resumeMode) {
                $log = CrmSyncLog::where('step', $stepName)->first();
                if ($log && $log->isCompletedToday()) {
                    $this->line("  <fg=gray>[SKIP]  {$stepName} — done today at {$log->completed_at}</>");
                    continue;
                }
            }

            // --dry-run: print what would run without executing
            if ($this->option('dry-run')) {
                $this->line("  [DRY]  {$stepName}: php artisan {$command} {$options}");
                continue;
            }

            $this->info("┌─ [{$stepName}] {$description}");

            // Mark step as running in sync log
            CrmSyncLog::updateOrCreate(
                ['step' => $stepName],
                [
                    'status'       => 'running',
                    'started_at'   => now(),
                    'completed_at' => null,
                    'last_error'   => null,
                    'attempts'     => DB::raw('attempts + 1'),
                ]
            );

            $startedAt = microtime(true);
            $exitCode  = $this->callStep($command, $options);
            $elapsed   = round(microtime(true) - $startedAt, 1);

            if ($exitCode === self::SUCCESS) {
                CrmSyncLog::where('step', $stepName)->update([
                    'status'       => 'done',
                    'completed_at' => now(),
                    'last_error'   => null,
                ]);
                $this->info("└─ [OK]   {$stepName} completed in {$elapsed}s");
            } else {
                $errorMsg = "exit code {$exitCode}";
                CrmSyncLog::where('step', $stepName)->update([
                    'status'     => 'failed',
                    'last_error' => $errorMsg,
                ]);
                $this->error("└─ [FAIL] {$stepName} failed ({$errorMsg}) after {$elapsed}s");
                Log::error("crm:sync-all step={$stepName} failed", ['exit_code' => $exitCode]);
                $overallFailed = true;
                // Continue to next step — a failed attendance sync should not block collections
            }

            $this->line('');
        }

        if ($overallFailed) {
            $this->warn('One or more steps failed.');
            $this->warn('To retry failed steps: php artisan crm:sync-all --resume');
            $this->warn('To restart from a step: php artisan crm:sync-all --from=STEP_NAME');
            $this->warn('Step names: ' . implode(', ', array_keys(self::STEPS)));
            return self::FAILURE;
        }

        $this->printFooter();
        return self::SUCCESS;
    }

    /**
     * Parse "--all --months=2 --delay=600" into an options array
     * and call the command via $this->call() so output streams live to the terminal.
     */
    private function callStep(string $command, string $options): int
    {
        $params = [];
        foreach (array_filter(explode(' ', trim($options))) as $token) {
            $token = ltrim($token, '-');
            if (str_contains($token, '=')) {
                [$key, $val] = explode('=', $token, 2);
                $params["--{$key}"] = $val;
            } elseif (!empty($token)) {
                $params["--{$token}"] = true;
            }
        }

        // $this->call() streams output live — Artisan::call() swallows it
        return $this->call($command, $params);
    }

    private function printHeader(): void
    {
        $ts = Carbon::now('Africa/Casablanca')->format('Y-m-d H:i:s');
        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info("║  CRM SYNC ALL  —  {$ts}  ║");
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');
    }

    private function printFooter(): void
    {
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║  SYNC COMPLETE — all dashboards updated      ║');
        $this->info('╚══════════════════════════════════════════════╝');
    }
}
