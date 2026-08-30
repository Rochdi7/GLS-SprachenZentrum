<?php

namespace App\Console\Commands;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sync session-presence from CRM bulk API into crm_attendance.
 *
 * Uses /api/external/v1/bulk/session-presence which supports dateFrom/dateTo,
 * unlike /session-presence which only accepts a single date.
 */
class SyncCrmAttendanceCommand extends Command
{
    protected $signature = 'crm:sync-attendance
        {--store=* : Ignored — bulk endpoint streams all stores in one pass}
        {--all : Ignored — bulk endpoint streams all stores in one pass}
        {--months=2 : Number of months back to sync (default 2, ignored if --days is set)}
        {--days= : Number of days back to sync instead of whole months (e.g. --days=2 for yesterday+today)}
        {--delay=500 : Delay between page requests in ms}
        {--max-pages=200 : Max pages per run to avoid OOM on shared hosting (default 200)}
        {--from-page=0 : Start from this page (for manual resume)}
        {--size=500 : Rows per page (lower this if the API times out generating large pages)}';

    protected $description = 'Sync CRM session-presence (attendance) to local crm_attendance table';

    private const LOCK_KEY  = 'crm.sync-attendance.lock';
    private const LOCK_TTL  = 3600;
    private const PAGE_SIZE = 500;   // default; override with --size
    private const BACKOFF   = [5, 15, 30, 60, 120];

    public function __construct(protected Crm $crm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (Cache::has(self::LOCK_KEY)) {
            $this->warn('[BLOCKED] Another attendance sync is already running.');
            return self::FAILURE;
        }

        Cache::put(self::LOCK_KEY, true, self::LOCK_TTL);

        try {
            return $this->sync();
        } finally {
            Cache::forget(self::LOCK_KEY);
        }
    }

    private function sync(): int
    {
        $delayMs  = max(100, (int) $this->option('delay'));
        $maxPages = max(1, (int) $this->option('max-pages'));
        $fromPage = max(0, (int) $this->option('from-page'));
        $pageSize = max(50, min(500, (int) $this->option('size')));

        $days = $this->option('days');

        if ($days !== null) {
            $days     = max(1, (int) $days);
            $dateFrom = Carbon::today('Africa/Casablanca')->subDays($days - 1)->toDateString();
        } else {
            $months   = max(1, min(12, (int) $this->option('months')));
            $dateFrom = Carbon::today('Africa/Casablanca')->subMonths($months)->startOfMonth()->toDateString();
        }

        $dateTo = Carbon::today('Africa/Casablanca')->toDateString();

        $this->info("Syncing attendance {$dateFrom} → {$dateTo} (bulk endpoint, all stores, pages {$fromPage}–" . ($fromPage + $maxPages - 1) . ", size={$pageSize})");

        $synced = $this->syncAll($dateFrom, $dateTo, $delayMs, $maxPages, $fromPage, $pageSize);

        Cache::flush();

        $this->info("[DONE] Total records synced: {$synced}");
        return self::SUCCESS;
    }

    private function syncAll(string $dateFrom, string $dateTo, int $delayMs, int $maxPages, int $fromPage, int $pageSize): int
    {
        // Global classMap: Wimschool CLASS_ID → our crm_classes.crm_id (FK in attendance).
        // Bulk endpoint streams ALL stores mixed — one pass covers everything.
        $classMap = CrmClass::whereNotNull('class_id')
            ->pluck('crm_id', 'class_id')
            ->toArray();

        $page        = $fromPage;
        $synced      = 0;
        $hasMore     = true;
        $pagesRun    = 0;
        $failedPages = [];

        while ($hasMore && $pagesRun < $maxPages) {
            $this->line("[ALL] Page {$page}...");

            try {
                $response = $this->fetchWithBackoff($dateFrom, $dateTo, $page, $pageSize);
            } catch (\Throwable $e) {
                // All retries exhausted for this page. Record it and keep going —
                // one bad page must not abandon the remaining window. Failed pages
                // are replayed at the end of the run.
                $this->error("[ALL] Page {$page} failed after retries: " . $e->getMessage());
                Log::error('crm:sync-attendance page failed', ['page' => $page, 'error' => $e->getMessage()]);
                $failedPages[] = $page;
                $page++;
                $pagesRun++;
                if ($delayMs > 0) usleep($delayMs * 1000);
                continue;
            }

            $rows     = $response['data'] ?? [];

            $this->line("[ALL] Page {$page} — " . count($rows) . " rows");

            $synced += $this->persistRows($rows, $classMap);

            $pagination = $response['pagination'] ?? [];
            $hasMore    = ($pagination['hasMore'] ?? false) || ($pagination['hasNext'] ?? false);
            $page++;
            $pagesRun++;

            if ($hasMore && $delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        // Second pass — replay pages that failed on the first sweep, after a
        // cool-down. The API is usually back by now; upserts make this safe to
        // repeat (unique key: crm_class_id + crm_student_id + date).
        if (!empty($failedPages)) {
            $this->warn('[ALL] Replaying ' . count($failedPages) . ' failed page(s) after 60s cool-down...');
            sleep(60);

            $stillFailed = [];
            foreach ($failedPages as $fp) {
                $this->line("[RETRY] Page {$fp}...");
                try {
                    $response = $this->fetchWithBackoff($dateFrom, $dateTo, $fp, $pageSize);
                    $synced  += $this->persistRows($response['data'] ?? [], $classMap);
                    $this->info("[RETRY] Page {$fp} recovered");
                } catch (\Throwable $e) {
                    $stillFailed[] = $fp;
                    $this->error("[RETRY] Page {$fp} still failing: " . $e->getMessage());
                }
                if ($delayMs > 0) usleep($delayMs * 1000);
            }

            if (!empty($stillFailed)) {
                $this->error('[ALL] Pages permanently failed this run: ' . implode(', ', $stillFailed));
                Log::error('crm:sync-attendance pages permanently failed', ['pages' => $stillFailed]);
            }
        }

        if ($hasMore) {
            $this->warn("[ALL] Page limit reached. Resume with: --from-page={$page} --max-pages={$maxPages}");
            Log::info('crm:sync-attendance page limit reached', ['next_page' => $page]);
        }

        return $synced;
    }

    /**
     * Map raw API rows to attendance records and upsert them.
     *
     * Shared by the main page sweep and the failed-page replay pass. The upsert
     * key (crm_class_id + crm_student_id + date) makes repeated calls idempotent,
     * so replaying a page can never create duplicates.
     */
    private function persistRows(array $rows, array $classMap): int
    {
        $upserts = [];
        $now     = now()->toDateTimeString();

        foreach ($rows as $row) {
            $sessionDate = $row['SESSION_DATE'] ?? null;
            $studentId   = $row['STUDENT_ID']   ?? null;
            $classId     = $row['CLASS_ID']      ?? null;

            if (!$sessionDate || !$studentId || !$classId) continue;

            try {
                $date = Carbon::parse($sessionDate)
                    ->setTimezone('Africa/Casablanca')
                    ->toDateString();
            } catch (\Throwable) {
                continue;
            }

            $crmClassId = $classMap[$classId] ?? null;
            if (!$crmClassId) continue;

            $isPresent = ($row['PRESENCE'] ?? 'N') === 'Y'
                || ($row['PRESENCE_STATUS'] ?? 0) == 1;

            // Normalize date_creation — NULL means draft, non-NULL means saisie
            // Replace ISO 8601 'T' separator with space so MariaDB accepts it
            $rawDc = $row['DATE_CREATION'] ?? null;
            $dateCreation = ($rawDc && $rawDc !== 'null')
                ? str_replace('T', ' ', $rawDc)
                : null;

            $upserts[] = [
                'crm_class_id'      => $crmClassId,
                'crm_student_id'    => $studentId,
                'date'              => $date,
                'crm_id'            => $row['SESSION_ID'] ?? null,
                'is_present'        => $isPresent ? 1 : 0,
                'raw_data'          => json_encode($row),
                // Normalized columns (avoids JSON_EXTRACT in WHERE clauses)
                'date_creation'     => $dateCreation,
                'session_reference' => $row['SESSION_REFERENCE'] ?? null,
                'last_synced_at'    => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        if (empty($upserts)) return 0;

        foreach (array_chunk($upserts, 200) as $chunk) {
            CrmAttendance::upsert(
                $chunk,
                ['crm_class_id', 'crm_student_id', 'date'],
                ['crm_id', 'is_present', 'raw_data', 'date_creation', 'session_reference', 'last_synced_at', 'updated_at']
            );
        }

        return count($upserts);
    }

    private function fetchWithBackoff(string $dateFrom, string $dateTo, int $page, int $pageSize = self::PAGE_SIZE): array
    {
        foreach (self::BACKOFF as $attempt => $waitSec) {
            try {
                return $this->crm->client()->get(
                    '/api/external/v1/bulk/session-presence',
                    [
                        'startDate'    => $dateFrom,
                        'endDate'      => $dateTo,
                        'page'         => $page,
                        'size'         => $pageSize,
                        'includeTotal' => 'false',
                    ]
                );
            } catch (\Throwable $e) {
                $msg = $e->getMessage();

                // Retry on rate-limits AND on transient network failures
                // (cURL 28 timeout, connection reset, DNS, 502/503/504).
                // Previously only 429 was retried, so a single timeout aborted
                // the whole run mid-window and left the sync incomplete.
                $isRetryable = str_contains($msg, '429')
                    || str_contains($msg, 'RATE_LIMITED')
                    || str_contains($msg, 'cURL error 28')
                    || str_contains($msg, 'cURL error 7')
                    || str_contains($msg, 'cURL error 35')
                    || str_contains($msg, 'cURL error 52')
                    || str_contains($msg, 'cURL error 56')
                    || str_contains($msg, 'Operation timed out')
                    || str_contains($msg, 'unreachable')
                    || str_contains($msg, '502')
                    || str_contains($msg, '503')
                    || str_contains($msg, '504');

                if (!$isRetryable) throw $e;

                $this->warn("[ALL] transient error — retry " . ($attempt + 1) . " after {$waitSec}s");
                Log::warning('crm:sync-attendance transient error', ['page' => $page, 'attempt' => $attempt + 1, 'error' => $msg]);
                sleep($waitSec);
            }
        }

        // Final attempt after all backoffs exhausted
        return $this->crm->client()->get(
            '/api/external/v1/bulk/session-presence',
            [
                'startDate'    => $dateFrom,
                'endDate'      => $dateTo,
                'page'         => $page,
                'size'         => $pageSize,
                'includeTotal' => 'false',
            ]
        );
    }
}
