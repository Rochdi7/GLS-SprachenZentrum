<?php

namespace App\Console\Commands;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\Site;
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
        {--store=* : Store IDs to sync (omit for all)}
        {--all : Sync all stores}
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
        $all      = $this->option('all');
        $storeIds = $this->option('store');
        $months   = max(1, min(12, (int) $this->option('months')));
        $delayMs  = max(100, (int) $this->option('delay'));

        if (!$all && empty($storeIds)) {
            $this->error('Specify --all or --store=ID');
            return self::FAILURE;
        }

        $dateFrom = Carbon::today('Africa/Casablanca')->subMonths($months)->startOfMonth()->toDateString();
        $dateTo   = Carbon::today('Africa/Casablanca')->toDateString();

        $this->info("Syncing attendance {$dateFrom} → {$dateTo}");

        $query = Site::whereNotNull('crm_store_id');
        if (!$all) {
            $query->whereIn('crm_store_id', array_map('intval', $storeIds));
        }
        $sites = $query->get(['id', 'name', 'crm_store_id', 'crm_token']);

        if ($sites->isEmpty()) {
            $this->warn('No sites found.');
            return self::SUCCESS;
        }

        $totalSynced = 0;

        foreach ($sites as $site) {
            $storeId = (int) $site->crm_store_id;
            $this->info("[STORE #{$storeId}] {$site->name}");

            $crm = $site->crm_token
                ? $this->crm->withToken($site->crm_token)
                : $this->crm;

            try {
                $synced = $this->syncStore($crm, $storeId, $dateFrom, $dateTo, $delayMs);
                $totalSynced += $synced;
                $this->info("[STORE #{$storeId}] Done — {$synced} records synced");
            } catch (\Throwable $e) {
                $this->error("[STORE #{$storeId}] FAILED: {$e->getMessage()}");
                Log::warning("crm:sync-attendance store #{$storeId} failed: " . $e->getMessage());
            }
        }

        // Bust presence suivi cache after sync
        Cache::flush();

        $this->info("[DONE] Total records synced: {$totalSynced}");
        return self::SUCCESS;
    }

    private function syncStore(Crm $crm, int $storeId, string $dateFrom, string $dateTo, int $delayMs): int
    {
        // Build class_id → crm_id map for this store
        $classMap = CrmClass::where('site_id', $storeId)
            ->whereNotNull('class_id')
            ->pluck('crm_id', 'class_id')
            ->toArray();

        $page    = 0;
        $synced  = 0;
        $hasMore = true;

        while ($hasMore) {
            $this->line("[STORE #{$storeId}] Page {$page}...");

            $response = $this->fetchWithBackoff($crm, $storeId, $dateFrom, $dateTo, $page);
            $rows     = $response['data'] ?? [];

            $this->line("[STORE #{$storeId}] Page {$page} — " . count($rows) . " rows");

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
                $rawDc = $row['DATE_CREATION'] ?? null;
                $dateCreation = ($rawDc && $rawDc !== 'null') ? $rawDc : null;

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

    private function fetchWithBackoff(Crm $crm, int $storeId, string $dateFrom, string $dateTo, int $page): array
    {
        foreach (self::BACKOFF as $attempt => $waitSec) {
            try {
                // Use bulk endpoint (500/page) instead of standard endpoint (25/page)
                // Reduces request count from ~320 to ~16 for 8,000 attendance rows
                return $crm->client()->get(
                    '/api/external/v1/bulk/session-presence',
                    [
                        'strStoreId'   => $storeId,
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

                $this->warn("[STORE #{$storeId}] 429 — retry " . ($attempt + 1) . " after {$waitSec}s");
                sleep($waitSec);
            }
        }

        // Final attempt after all backoffs exhausted
        return $crm->client()->get(
            '/api/external/v1/bulk/session-presence',
            [
                'strStoreId'   => $storeId,
                'startDate'    => $dateFrom,
                'endDate'      => $dateTo,
                'page'         => $page,
                'size'         => self::PAGE_SIZE,
                'includeTotal' => 'false',
            ]
        );
    }
}
