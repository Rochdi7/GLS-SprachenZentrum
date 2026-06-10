<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\SiteExpense;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sync expenses from the Wimschool CRM API into site_expenses.
 *
 * Maps /api/external/v1/expenses rows to SiteExpense using crm_expense_id
 * as the upsert key so re-syncing is idempotent.
 *
 * Usage:
 *   php artisan crm:sync-expenses --all
 *   php artisan crm:sync-expenses --all --from=2025-09-01
 *   php artisan crm:sync-expenses --all --months=10        (10 months back from today)
 */
class SyncCrmExpensesCommand extends Command
{
    protected $signature = 'crm:sync-expenses
        {--all         : Sync all sites}
        {--store=*     : Specific crm_store_id values}
        {--from=       : Start date ISO yyyy-MM-dd (default: 9 months back)}
        {--months=     : How many months back from today (overrides --from)}
        {--delay=500   : Delay between pages in ms}
        {--dump        : Dump first raw API row and exit (for field discovery)}';

    protected $description = 'Mirror CRM expenses to local site_expenses table';

    private const LOCK_KEY  = 'crm.sync-expenses.lock';
    private const LOCK_TTL  = 1800;
    private const PAGE_SIZE = 25;
    private const BACKOFF   = [5, 15, 30];

    public function __construct(protected Crm $crm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (Cache::has(self::LOCK_KEY)) {
            $this->warn('[BLOCKED] crm:sync-expenses is already running.');
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

        // --dump: fetch one page from first store and print raw row keys/values
        if ($this->option('dump')) {
            return $this->dumpFirstRow($all, $storeIds);
        }

        // Resolve date range
        $monthsOption = $this->option('months');
        $fromOption   = $this->option('from');

        if ($monthsOption !== null) {
            $startDate = Carbon::now('Africa/Casablanca')->subMonths((int) $monthsOption)->startOfMonth()->toDateString();
        } elseif ($fromOption !== null) {
            $startDate = Carbon::parse($fromOption)->toDateString();
        } else {
            // Default: September 2025 (first month of data)
            $startDate = '2025-09-01';
        }

        $endDate = Carbon::now('Africa/Casablanca')->endOfMonth()->toDateString();

        $this->info("[crm:sync-expenses] Range: {$startDate} → {$endDate}");

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
            $this->info("[STORE #{$storeId}] {$site->name}");

            $crm = $site->crm_token
                ? $this->crm->withToken($site->crm_token)
                : $this->crm;

            try {
                $synced = $this->syncStore($crm, $site->id, $storeId, $site->name, $startDate, $endDate, $delayMs);
                $totalSynced += $synced;
                $this->info("[STORE #{$storeId}] Done — {$synced} expenses synced");
            } catch (\Throwable $e) {
                $this->error("[STORE #{$storeId}] FAILED: {$e->getMessage()}");
                Log::warning("crm:sync-expenses store #{$storeId} failed: {$e->getMessage()}");
            }
        }

        $this->info("[DONE] Total expenses synced: {$totalSynced}");
        return self::SUCCESS;
    }

    private function syncStore(
        Crm $crm,
        int $localSiteId,
        int $storeId,
        string $storeName,
        string $startDate,
        string $endDate,
        int $delayMs,
    ): int {
        $page    = 0;
        $synced  = 0;
        $hasMore = true;

        while ($hasMore) {
            $this->line("[STORE #{$storeId}] Fetching page {$page}...");

            $response = $this->fetchWithBackoff($crm, $storeId, $page, $startDate, $endDate);
            $rows     = $response['data'] ?? [];

            $this->line("[STORE #{$storeId}] Page {$page} — " . count($rows) . " rows");

            if (!empty($rows)) {
                $this->upsertRows($rows, $localSiteId, $storeId);
                $synced += count($rows);
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

    private function upsertRows(array $rows, int $localSiteId, int $storeId): void
    {
        $now     = now()->toDateTimeString();
        $upserts = [];

        foreach ($rows as $row) {
            $crmId = (string) ($row['ID'] ?? '');
            if ($crmId === '') continue;

            // DATE arrives as UTC ISO datetime — convert to Casablanca local date
            $expenseDate = null;
            $month       = null;
            $rawDate     = $row['DATE'] ?? null;
            if ($rawDate) {
                try {
                    $parsed      = Carbon::parse($rawDate)->setTimezone('Africa/Casablanca');
                    $expenseDate = $parsed->toDateString();
                    $month       = $parsed->startOfMonth()->toDateString();
                } catch (\Throwable) {}
            }
            if (!$month) {
                $month = now('Africa/Casablanca')->startOfMonth()->toDateString();
            }

            // EXPENSE_TYPE_NAME → our normalised type key
            $rawType = (string) ($row['EXPENSE_TYPE_NAME'] ?? 'autre');
            $type    = SiteExpense::normalizeType($rawType);

            $upserts[] = [
                'crm_expense_id'  => $crmId,
                'crm_source'      => 'wimschool',
                'site_id'         => $localSiteId,
                'type'            => $type,
                'label'           => (string) ($row['DESCRIPTION'] ?? $rawType),
                'amount'          => (float) ($row['TOTAL_AMOUNT'] ?? 0),
                'month'           => $month,
                'expense_date'    => $expenseDate,
                'reference'       => $row['REFERENCE'] ?? null,
                'payment_method'  => $row['PAYMENT_METHOD_ID'] !== null
                                        ? (string) $row['PAYMENT_METHOD_ID']
                                        : null,
                'operator_name'   => $row['USER_FULLNAME_CREATION'] ?? null,
                'notes'           => ($row['INVOICE_REFERENCE'] ?? '') !== ''
                                        ? (string) $row['INVOICE_REFERENCE']
                                        : null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if (empty($upserts)) return;

        SiteExpense::upsert(
            $upserts,
            ['crm_expense_id'],
            ['type', 'label', 'amount', 'month', 'expense_date', 'reference',
             'payment_method', 'operator_name', 'notes', 'updated_at'],
        );
    }

    private function dumpFirstRow(bool $all, array $storeIds): int
    {
        $query = Site::whereNotNull('crm_store_id')->where('crm_store_id', '>', 0);
        if (!$all) {
            $query->whereIn('crm_store_id', array_map('intval', $storeIds));
        }
        $site = $query->first(['id', 'name', 'crm_store_id', 'crm_token']);

        if (!$site) {
            $this->error('No site found.');
            return self::FAILURE;
        }

        $crm = $site->crm_token ? $this->crm->withToken($site->crm_token) : $this->crm;
        $response = $crm->expenses()->list(page: 0, size: 1, strStoreId: (int) $site->crm_store_id);
        $rows = $response['data'] ?? [];

        if (empty($rows)) {
            $this->warn('No rows returned.');
            return self::SUCCESS;
        }

        $row = $rows[0];
        $this->info('=== RAW API FIELDS (first row) ===');
        foreach ($row as $key => $value) {
            $this->line(sprintf('  %-40s %s', $key, json_encode($value)));
        }
        $this->info('=== END ===');
        return self::SUCCESS;
    }

    private function fetchWithBackoff(
        Crm $crm,
        int $storeId,
        int $page,
        string $startDate,
        string $endDate,
    ): array {
        foreach (self::BACKOFF as $attempt => $waitSec) {
            try {
                return $crm->expenses()->list(
                    page: $page,
                    size: self::PAGE_SIZE,
                    includeTotal: false,
                    strStoreId: $storeId,
                    startDate: $startDate,
                    endDate: $endDate,
                );
            } catch (\Throwable $e) {
                $is429 = str_contains($e->getMessage(), '429')
                    || str_contains($e->getMessage(), 'rate limit')
                    || str_contains($e->getMessage(), 'RATE_LIMITED');

                if (!$is429) throw $e;

                $retryNum = $attempt + 1;
                $this->warn("[STORE #{$storeId}] 429 rate limit — retry {$retryNum}, waiting {$waitSec}s...");
                Log::warning("crm:sync-expenses 429 on store #{$storeId} page {$page}, retry {$retryNum}");
                sleep($waitSec);
            }
        }

        return $crm->expenses()->list(
            page: $page,
            size: self::PAGE_SIZE,
            includeTotal: false,
            strStoreId: $storeId,
            startDate: $startDate,
            endDate: $endDate,
        );
    }
}
