<?php

namespace App\Console\Commands;

use App\Models\CrmCollectionRow;
use App\Models\Site;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncCrmCollectionsCommand extends Command
{
    protected $signature = 'crm:sync-collections
        {--store=* : Store IDs to sync (omit for all)}
        {--all : Sync all stores}
        {--delay=400 : Delay between pages in ms}';

    protected $description = 'Safely sync CRM payment-collection (receivables) to local DB';

    private const LOCK_KEY  = 'crm.sync-collections.lock';
    private const LOCK_TTL  = 1800;
    private const PAGE_SIZE = 500;
    private const BACKOFF   = [5, 15, 30];

    public function __construct(protected Crm $crm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (Cache::has(self::LOCK_KEY)) {
            $this->warn('[BLOCKED] Another collections sync is already running.');
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
        $delayMs  = max(100, (int) $this->option('delay'));

        if (!$all && empty($storeIds)) {
            $this->error('Specify --all or --store=ID');
            return self::FAILURE;
        }

        $query = Site::whereNotNull('crm_store_id')->where('crm_store_id', '>', 0);
        if (!$all) {
            $query->whereIn('crm_store_id', array_map('intval', $storeIds));
        }
        $sites = $query->get(['id', 'name', 'crm_store_id', 'crm_token']);

        if ($sites->isEmpty()) {
            $this->warn('No sites with crm_store_id found.');
            return self::SUCCESS;
        }

        $totalSynced = 0;

        foreach ($sites as $site) {
            $storeId = (int) $site->crm_store_id;
            $this->info("[STORE #{$storeId}] {$site->name} — syncing collections");

            $crm = $site->crm_token
                ? $this->crm->withToken($site->crm_token)
                : $this->crm;

            try {
                $synced = $this->syncStore($crm, $storeId, $site->name, $delayMs);
                $totalSynced += $synced;
                $this->info("[STORE #{$storeId}] Done — {$synced} collection rows synced");
            } catch (\Throwable $e) {
                $this->error("[STORE #{$storeId}] FAILED: {$e->getMessage()}");
                Log::warning("crm:sync-collections store #{$storeId} failed: {$e->getMessage()}");
            }
        }

        $this->info("[DONE] Total collection rows synced: {$totalSynced}");
        return self::SUCCESS;
    }

    private function syncStore(Crm $crm, int $storeId, string $storeName, int $delayMs): int
    {
        $page    = 0;
        $synced  = 0;
        $hasMore = true;

        while ($hasMore) {
            $this->line("[STORE #{$storeId}] Fetching page {$page}...");

            $response = $this->fetchWithBackoff($crm, $storeId, $page);
            $rows     = $response['data'] ?? [];

            $this->line("[STORE #{$storeId}] Page {$page} — " . count($rows) . " rows");

            $upserts = [];
            $now     = now()->toDateTimeString();

            foreach ($rows as $row) {
                if (empty($row['ID'])) continue;

                // DUE_DATE arrives as ISO datetime e.g. "2025-08-31T23:00:00.000Z" — normalise to Y-m-d
                $rawDue  = $row['DUE_DATE'] ?? null;
                $dueDate = null;
                if ($rawDue) {
                    try {
                        $dueDate = Carbon::parse($rawDue)->setTimezone('Africa/Casablanca')->toDateString();
                    } catch (\Throwable) {}
                }

                $upserts[] = [
                    'crm_id'                  => (string) $row['ID'],
                    'crm_store_id'            => $row['STR_STORE_ID'] ?? $storeId,
                    'student_id'              => $row['STUDENT_ID'] ?? null,
                    'student_name'            => $row['STUDENT_FULL_NAME'] ?? null,
                    'store_name'              => $storeName,
                    'total_price'             => $row['TOTAL_PRICE'] ?? null,
                    'rest_amount'             => $row['REST_AMOUNT'] ?? null,
                    'due_date'                => $dueDate,
                    'payment_delay_days'      => $row['PAYMENT_DELAY_DAYS'] ?? null,
                    'registration_id'         => $row['REGISTRATION_ID'] ?? null,
                    'registration_status_id'  => $row['REGISTRATION_STATUS_ID'] ?? null,
                    'registration_status_name'=> $row['REGISTRATION_STATUS_NAME'] ?? null,
                    'service_type_name'       => $row['SERVICE_TYPE_NAME'] ?? null,
                    'class_id'                => $row['CLASS_ID'] ?? null,
                    'raw_data'                => json_encode($row),
                    'last_synced_at'          => $now,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }

            if (!empty($upserts)) {
                CrmCollectionRow::upsert(
                    $upserts,
                    ['crm_id'],
                    ['crm_store_id', 'student_id', 'student_name', 'store_name', 'total_price',
                     'rest_amount', 'due_date', 'payment_delay_days', 'registration_id',
                     'registration_status_id', 'registration_status_name', 'service_type_name',
                     'class_id', 'raw_data', 'last_synced_at', 'updated_at']
                );
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

    private function fetchWithBackoff(Crm $crm, int $storeId, int $page): array
    {
        foreach (self::BACKOFF as $attempt => $waitSec) {
            try {
                return $crm->payments()->bulkCollection(
                    page: $page,
                    size: self::PAGE_SIZE,
                    includeTotal: false,
                    strStoreId: $storeId,
                );
            } catch (\Throwable $e) {
                $is429 = str_contains($e->getMessage(), '429')
                    || str_contains($e->getMessage(), 'rate limit')
                    || str_contains($e->getMessage(), 'RATE_LIMITED');

                if (!$is429) throw $e;

                $retryNum = $attempt + 1;
                $this->warn("[STORE #{$storeId}] 429 rate limit — retry {$retryNum}, waiting {$waitSec}s...");
                Log::warning("crm:sync-collections 429 on store #{$storeId} page {$page}, retry {$retryNum}");
                sleep($waitSec);
            }
        }

        return $crm->payments()->bulkCollection(
            page: $page,
            size: self::PAGE_SIZE,
            includeTotal: false,
            strStoreId: $storeId,
        );
    }
}
