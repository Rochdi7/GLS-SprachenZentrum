<?php

namespace App\Console\Commands;

use App\Models\CrmChurnScore;
use App\Models\Site;
use App\Services\Crm\Stats\ChurnScoringService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * php artisan crm:churn-scores
 *   --all          : compute for every configured CRM center (default behavior)
 *   --store=ID     : compute for a single store only
 *   --dry-run      : score & print without writing to DB
 *
 * This command reads ONLY local DB tables:
 *   crm_registrations, crm_attendance, crm_collection_rows
 * It NEVER calls the Wimschool API.
 *
 * Designed to run AFTER crm:sync-all so the data is fresh.
 * Slot: added as 'churn_scores' step in CrmSyncAllCommand::STEPS.
 */
class ComputeChurnScoresCommand extends Command
{
    protected $signature = 'crm:churn-scores
        {--all      : Compute for all configured centers (default)}
        {--store=   : Compute for a single store ID}
        {--dry-run  : Print results without writing to DB}';

    protected $description = 'Compute student churn/risk scores from local DB — no API calls';

    public function __construct(private readonly ChurnScoringService $scorer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $storeId = $this->option('store') ? (int) $this->option('store') : null;

        $this->info('');
        $this->info('┌─ [churn_scores] Computing student risk scores from local DB');

        if ($dryRun) {
            $this->warn('  DRY-RUN mode — nothing will be written');
        }

        $sites = $this->resolveSites($storeId);

        if ($sites->isEmpty()) {
            $this->warn('  No CRM stores configured. Run crm:sync-centers first.');
            return self::FAILURE;
        }

        $totalUpserted = 0;
        $totalErrors   = 0;
        $startedAt     = microtime(true);

        foreach ($sites as $site) {
            $sid = (int) $site->crm_store_id;
            $this->line("  ├─ {$site->name} (store {$sid})");

            try {
                $rows = $this->scorer->computeAll($sid);
                $count = count($rows);

                if ($count === 0) {
                    $this->line("  │  └─ No students found");
                    continue;
                }

                if ($dryRun) {
                    $this->previewRows($rows);
                    continue;
                }

                $this->upsertScores($rows);
                $totalUpserted += $count;

                $byLevel = collect($rows)->groupBy('risk_level')->map->count();
                $this->line(sprintf(
                    "  │  └─ %d scored | critical:%d high:%d medium:%d low:%d",
                    $count,
                    $byLevel->get('critical', 0),
                    $byLevel->get('high', 0),
                    $byLevel->get('medium', 0),
                    $byLevel->get('low', 0),
                ));

            } catch (\Throwable $e) {
                $totalErrors++;
                $this->error("  │  └─ ERROR: {$e->getMessage()}");
                Log::error('crm:churn-scores failed for store', [
                    'store_id' => $sid,
                    'error'    => $e->getMessage(),
                    'trace'    => $e->getTraceAsString(),
                ]);
            }
        }

        $elapsed = round(microtime(true) - $startedAt, 1);
        $this->info("└─ Done in {$elapsed}s — {$totalUpserted} scores upserted, {$totalErrors} errors");
        $this->info('');

        Log::info('crm:churn-scores completed', [
            'upserted' => $totalUpserted,
            'errors'   => $totalErrors,
            'elapsed'  => $elapsed,
        ]);

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolveSites(?int $storeId)
    {
        $q = Site::whereNotNull('crm_store_id');
        if ($storeId) {
            $q->where('crm_store_id', $storeId);
        }
        return $q->get();
    }

    /**
     * Bulk upsert using a single DB statement to avoid N+1 writes.
     * Matches on (crm_student_id, crm_store_id).
     */
    private function upsertScores(array $rows): void
    {
        $now = Carbon::now()->toDateTimeString();

        $payload = array_map(fn($r) => [
            'crm_student_id'  => $r['crm_student_id'],
            'crm_store_id'    => $r['crm_store_id'],
            'score'           => $r['score'],
            'risk_level'      => $r['risk_level'],
            'signals'         => json_encode($r['signals'], JSON_UNESCAPED_UNICODE),
            'student_name'    => $r['student_name'],
            'registration_id' => $r['registration_id'],
            'class_id'        => $r['class_id'],
            'computed_at'     => $r['computed_at'],
            'created_at'      => $now,
            'updated_at'      => $now,
        ], $rows);

        // Chunk to avoid hitting MySQL max_allowed_packet on large centers
        foreach (array_chunk($payload, 200) as $chunk) {
            DB::table('crm_churn_scores')->upsert(
                $chunk,
                ['crm_student_id', 'crm_store_id'],
                ['score', 'risk_level', 'signals', 'student_name', 'registration_id', 'class_id', 'computed_at', 'updated_at'],
            );
        }
    }

    private function previewRows(array $rows): void
    {
        $headers = ['Student', 'Score', 'Level', 'Reasons'];
        $tableData = collect($rows)->map(fn($r) => [
            $r['student_name'],
            $r['score'],
            strtoupper($r['risk_level']),
            implode('; ', $r['signals']['reasons'] ?? []),
        ])->all();

        $this->table($headers, $tableData);
    }
}
