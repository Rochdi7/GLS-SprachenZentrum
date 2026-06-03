<?php

namespace App\Console\Commands;

use App\Models\CrmRegistration;
use App\Models\Site;
use App\Services\Crm\Crm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncCrmRegistrationsCommand extends Command
{
    protected $signature = 'crm:sync-registrations
        {--store=* : Store IDs to sync (omit for all)}
        {--all : Sync all stores}
        {--delay=500 : Delay between pages in ms (default 500)}';

    protected $description = 'Safely sync CRM registrations to local DB with rate-limit handling';

    private const LOCK_KEY   = 'crm.sync-registrations.lock';
    private const LOCK_TTL   = 1800; // 30 min max
    private const PAGE_SIZE  = 25;
    private const BACKOFF    = [5, 15, 30]; // seconds per retry attempt

    public function __construct(protected Crm $crm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (Cache::has(self::LOCK_KEY)) {
            $this->warn('[BLOCKED] Another sync is already running. Exiting.');
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

        $query = Site::whereNotNull('crm_store_id');
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
            $this->info("[STORE #{$storeId}] {$site->name} — starting sync");

            $crm = $site->crm_token
                ? $this->crm->withToken($site->crm_token)
                : $this->crm;

            try {
                $synced = $this->syncStore($crm, $storeId, $delayMs);
                $totalSynced += $synced;
                $this->info("[STORE #{$storeId}] Done — {$synced} registrations synced");
            } catch (\Throwable $e) {
                $this->error("[STORE #{$storeId}] FAILED: {$e->getMessage()}");
                Log::warning("crm:sync-registrations store #{$storeId} failed: {$e->getMessage()}");
            }
        }

        $this->info("[DONE] Total registrations synced: {$totalSynced}");
        return self::SUCCESS;
    }

    private function syncStore(Crm $crm, int $storeId, int $delayMs): int
    {
        $page    = 0;
        $synced  = 0;
        $hasMore = true;

        while ($hasMore) {
            $this->line("[STORE #{$storeId}] Fetching page {$page}...");

            $response = $this->fetchWithBackoff($crm, $storeId, $page);

            $rows = $response['data'] ?? [];
            $this->line("[STORE #{$storeId}] Page {$page} — " . count($rows) . " rows received");

            foreach ($rows as $item) {
                if (empty($item['ID'])) continue;

                // Parse normalized date_creation from raw DATE_CREATION field
                $rawDc = $item['DATE_CREATION'] ?? null;
                $dateCreation = null;
                if ($rawDc && $rawDc !== 'null') {
                    try {
                        $dateCreation = \Carbon\Carbon::parse($rawDc)->toDateString();
                    } catch (\Throwable) {}
                }

                CrmRegistration::updateOrCreate(
                    ['crm_id' => $item['ID']],
                    [
                        'crm_student_id' => $item['STUDENT_ID'] ?? null,
                        'crm_class_id'   => $item['LEVEL_SESSION_ID'] ?? $item['CLASS_ID'] ?? null,
                        'crm_store_id'   => $storeId,
                        'status'         => $item['REGISTRATION_STATUS_NAME'] ?? $item['STATUS_NAME'] ?? null,
                        // Normalized columns — avoids JSON_EXTRACT in WHERE/GROUP BY
                        'date_creation'  => $dateCreation,
                        'status_label'   => $item['REGISTRATION_STATUS_NAME'] ?? null,
                        'raw_data'       => $item,
                        'last_synced_at' => now(),
                    ]
                );
                $synced++;
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
                return $crm->registrations()->list(
                    page: $page,
                    size: self::PAGE_SIZE,
                    includeTotal: false,
                    strStoreId: $storeId,
                );
            } catch (\Throwable $e) {
                $is429 = str_contains($e->getMessage(), '429')
                    || str_contains($e->getMessage(), 'rate limit')
                    || str_contains($e->getMessage(), 'RATE_LIMITED');

                if (!$is429) {
                    throw $e;
                }

                $retryNum = $attempt + 1;
                $this->warn("[STORE #{$storeId}] 429 rate limit — attempt {$retryNum}, waiting {$waitSec}s...");
                Log::warning("crm:sync-registrations 429 on store #{$storeId} page {$page}, retry {$retryNum} after {$waitSec}s");
                sleep($waitSec);
            }
        }

        // Final attempt after all backoffs
        return $crm->registrations()->list(
            page: $page,
            size: self::PAGE_SIZE,
            includeTotal: false,
            strStoreId: $storeId,
        );
    }
}
