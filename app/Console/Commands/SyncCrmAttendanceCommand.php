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
        {--months=2 : Number of months back to sync (default 2)}
        {--delay=500 : Delay between page requests in ms}';

    protected $description = 'Sync CRM session-presence (attendance) to local crm_attendance table';

    private const LOCK_KEY  = 'crm.sync-attendance.lock';
    private const LOCK_TTL  = 3600;
    private const PAGE_SIZE = 500; // bulk endpoint supports up to 500 (was 25 on standard endpoint — 20× improvement)
    private const BACKOFF   = [5, 15, 30];

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
        $months  = max(1, min(12, (int) $this->option('months')));
        $delayMs = max(100, (int) $this->option('delay'));

        $dateFrom = Carbon::today('Africa/Casablanca')->subMonths($months)->startOfMonth()->toDateString();
        $dateTo   = Carbon::today('Africa/Casablanca')->toDateString();

        $this->info("Syncing attendance {$dateFrom} → {$dateTo} (all stores, single bulk pass)");

        // The bulk endpoint ignores strStoreId and streams ALL stores in one paginated feed.
        // Running it per-store wastes requests and drops rows belonging to other stores.
        // One pass with the global classMap covers everything correctly.
        $synced = $this->syncAll($dateFrom, $dateTo, $delayMs);

        Cache::flush();

        $this->info("[DONE] Total records synced: {$synced}");
        return self::SUCCESS;
    }

    private function syncAll(string $dateFrom, string $dateTo, int $delayMs): int
    {
        // Build global class_id → crm_id map across ALL stores.
        // classMap key = Wimschool CLASS_ID, value = our crm_classes.crm_id (FK used in attendance).
        $classMap = CrmClass::whereNotNull('class_id')
            ->pluck('crm_id', 'class_id')
            ->toArray();

        $page    = 0;
        $synced  = 0;
        $hasMore = true;

        while ($hasMore) {
            $this->line("[ALL] Page {$page}...");

            $response = $this->fetchWithBackoff($dateFrom, $dateTo, $page);
            $rows     = $response['data'] ?? [];

            $this->line("[ALL] Page {$page} — " . count($rows) . " rows");

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

            if (!empty($upserts)) {
                foreach (array_chunk($upserts, 200) as $chunk) {
                    CrmAttendance::upsert(
                        $chunk,
                        ['crm_class_id', 'crm_student_id', 'date'],
                        ['crm_id', 'is_present', 'raw_data', 'date_creation', 'session_reference', 'last_synced_at', 'updated_at']
                    );
                }
                $synced += count($upserts);
            }

            $pagination = $response['pagination'] ?? [];
            $hasMore    = ($pagination['hasMore'] ?? false) || ($pagination['hasNext'] ?? false);
            $page++;

            if ($hasMore && $delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return $synced;
    }

    private function fetchWithBackoff(string $dateFrom, string $dateTo, int $page): array
    {
        foreach (self::BACKOFF as $attempt => $waitSec) {
            try {
                return $this->crm->client()->get(
                    '/api/external/v1/bulk/session-presence',
                    [
                        'startDate'    => $dateFrom,
                        'endDate'      => $dateTo,
                        'page'         => $page,
                        'size'         => self::PAGE_SIZE,
                        'includeTotal' => 'false',
                    ]
                );
            } catch (\Throwable $e) {
                $is429 = str_contains($e->getMessage(), '429')
                    || str_contains($e->getMessage(), 'RATE_LIMITED');

                if (!$is429) throw $e;

                $this->warn("[ALL] 429 — retry " . ($attempt + 1) . " after {$waitSec}s");
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
                'size'         => self::PAGE_SIZE,
                'includeTotal' => 'false',
            ]
        );
    }
}
