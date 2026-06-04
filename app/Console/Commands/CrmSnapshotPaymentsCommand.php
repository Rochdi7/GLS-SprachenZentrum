<?php

namespace App\Console\Commands;

use App\Models\CrmCollectionRow;
use App\Models\CrmPaymentSnapshot;
use App\Models\Site;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Pulls every payment row from the Homeschool CRM and stores a snapshot for
 * today's date. Idempotent: re-running on the same day updates rows instead
 * of duplicating.
 *
 * Uses bulk endpoints (size=500) — 20× fewer HTTP round-trips vs the standard
 * 25-row endpoints, which was causing OOM kills on shared hosting.
 *
 * After fetching payments, cross-references bulk payment-allocations
 * (PAYMENT_ID → REGISTRATION_ID) against crm_collection_rows
 * (REGISTRATION_ID → DUE_DATE / REST_AMOUNT) so each snapshot row carries
 * both the effective payment date AND the échéance (due date).
 *
 * Run nightly via the scheduler. The diff page compares two consecutive days
 * to detect deletions / amount changes / late edits.
 */
class CrmSnapshotPaymentsCommand extends Command
{
    protected $signature = 'crm:snapshot-payments
        {--store=*   : Store IDs to snapshot (repeatable, default: all configured centers)}
        {--date=     : Snapshot date (default: today, format YYYY-MM-DD)}
        {--months=2  : How many months back to fetch (default: 2, covers full previous month)}
        {--pause=30  : Seconds to sleep between centers (increase to 90 during backfill)}';

    protected $description = 'Capture daily snapshot of all CRM payments (+ due dates) for fraud detection.';

    private const BULK_SIZE           = 500;
    private const SLEEP_BETWEEN_PAGES = 2;

    public function handle(Crm $crm): int
    {
        $date     = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : Carbon::today()->toDateString();

        $months       = max(1, (int) $this->option('months'));
        $pauseSecs    = max(10, (int) $this->option('pause'));
        $storeIds     = array_map('intval', array_filter((array) $this->option('store')));

        $sites = Site::whereNotNull('crm_store_id')->where('crm_store_id', '>', 0)
            ->when(!empty($storeIds), fn ($q) => $q->whereIn('crm_store_id', $storeIds))
            ->get();

        if ($sites->isEmpty()) {
            $this->error('No sites with crm_store_id configured.');
            return self::FAILURE;
        }

        $this->info("Snapshotting payments for {$date} across {$sites->count()} center(s) [bulk, size=" . self::BULK_SIZE . ", pause={$pauseSecs}s]...");

        $totalCaptured = 0;
        $totalErrors   = 0;

        foreach ($sites as $i => $site) {
            if ($i > 0) {
                $this->line("  (pause {$pauseSecs}s between centers...)");
                sleep($pauseSecs);
            }

            $this->line("  → {$site->name} (#{$site->crm_store_id})");

            try {
                $captured = $this->snapshotCenter($crm, $site, $date, $months);
                $totalCaptured += $captured;
                $this->line("    captured {$captured} payments");
            } catch (CrmException $e) {
                $this->error("    FAILED: {$e->getMessage()}");
                $totalErrors++;
            }
        }

        $this->newLine();
        $this->info("Done. Captured {$totalCaptured} payments. {$totalErrors} center(s) failed.");

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function snapshotCenter(Crm $crm, Site $site, string $date, int $months = 2): int
    {
        $startDate = Carbon::parse($date)->subMonths($months)->startOfMonth()->toDateString();

        // Step 1 — bulk-fetch all payments for this center/window
        $payments = $this->fetchAllPagesBulk(
            fn (int $page) => $crm->payments()->bulkList(
                page: $page,
                size: self::BULK_SIZE,
                strStoreId: $site->crm_store_id,
                startDate: $startDate,
                endDate: $date,
            ),
            label: "payments/{$site->crm_store_id}"
        );

        $this->line("    fetched " . count($payments) . " payments");

        // Step 2 — bulk-fetch payment-allocations for the same window
        // so we can resolve PAYMENT_ID → REGISTRATION_ID → DUE_DATE / REST_AMOUNT
        sleep(3);
        $allocations = $this->fetchAllPagesBulk(
            fn (int $page) => $crm->payments()->bulkAllocations(
                page: $page,
                size: self::BULK_SIZE,
                strStoreId: $site->crm_store_id,
                startDate: $startDate,
                endDate: $date,
            ),
            label: "allocations/{$site->crm_store_id}"
        );

        $this->line("    fetched " . count($allocations) . " allocations");

        // Build map: payment_id → registration_id
        $paymentToReg = [];
        foreach ($allocations as $alloc) {
            $pid = (int) ($alloc['PAYMENT_ID'] ?? 0);
            $rid = (int) ($alloc['REGISTRATION_ID'] ?? 0);
            if ($pid && $rid) {
                $paymentToReg[$pid] = $rid;
            }
        }

        // Step 3 — load collection rows for this store from local mirror
        // keyed by registration_id → [due_date, rest_amount]
        $regIds = array_values(array_unique(array_filter(array_values($paymentToReg))));
        $collectionByReg = [];
        if (!empty($regIds)) {
            CrmCollectionRow::where('crm_store_id', $site->crm_store_id)
                ->whereIn('registration_id', $regIds)
                ->get(['registration_id', 'due_date', 'rest_amount'])
                ->each(function ($row) use (&$collectionByReg) {
                    $collectionByReg[(int) $row->registration_id] = [
                        'due_date'    => $row->due_date?->toDateString(),
                        'rest_amount' => $row->rest_amount,
                    ];
                });
        }

        // Step 4 — load existing hashes for this store+date so we skip unchanged rows
        $paymentIds = array_map(fn ($r) => $r['ID'] ?? 0, $payments);
        $existingHashes = CrmPaymentSnapshot::where('snapshot_date', $date)
            ->where('crm_store_id', $site->crm_store_id)
            ->whereIn('crm_payment_id', $paymentIds)
            ->pluck('payload_hash', 'crm_payment_id')
            ->toArray();

        // Step 5 — upsert only new or changed rows
        $captured = 0;
        $skipped  = 0;
        foreach ($payments as $row) {
            $pid  = (int) ($row['ID'] ?? 0);
            $hash = hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

            // Skip if payload unchanged AND echéance already resolved
            if (isset($existingHashes[$pid]) && $existingHashes[$pid] === $hash) {
                $skipped++;
                continue;
            }

            $rid      = $paymentToReg[$pid] ?? null;
            $echeance = $rid ? ($collectionByReg[$rid] ?? []) : [];

            $this->upsertSnapshot($row, $date, $rid, $echeance, $hash);
            $captured++;
        }

        $this->line("    {$captured} written, {$skipped} unchanged (skipped)");
        return $captured + $skipped;
    }

    /**
     * Paginate through a bulk CRM endpoint (size up to 500).
     * Sleeps SLEEP_BETWEEN_PAGES between pages (not the first).
     */
    protected function fetchAllPagesBulk(callable $fetcher, string $label): array
    {
        $page = 0;
        $all  = [];

        do {
            if ($page > 0) {
                sleep(self::SLEEP_BETWEEN_PAGES);
            }

            $response = $fetcher($page);
            $rows     = $response['data'] ?? [];
            $all      = array_merge($all, $rows);

            $this->line("    [{$label}] page {$page} — " . count($rows) . " rows");

            $hasNext = $response['pagination']['hasNext']
                ?? $response['pagination']['hasMore']
                ?? false;
            $page++;
        } while ($hasNext && $page < 50); // 50 × 500 = 25k rows safety cap per center

        return $all;
    }

    protected function upsertSnapshot(array $row, string $date, ?int $registrationId, array $echeance, string $hash): void
    {

        CrmPaymentSnapshot::updateOrCreate(
            [
                'crm_payment_id' => $row['ID'],
                'snapshot_date'  => $date,
            ],
            [
                'crm_store_id'            => $row['STR_STORE_ID']            ?? null,
                'student_id'              => $row['STUDENT_ID']              ?? null,
                'registration_id'         => $registrationId,
                'reference'               => $row['REFERENCE']               ?? null,
                'amount'                  => $row['AMOUNT']                  ?? null,
                'rest_amount'             => $echeance['rest_amount']        ?? null,
                'effective_date'          => $row['EFFECTIVE_DATE']          ?? null,
                'due_date'                => $echeance['due_date']           ?? null,
                'payment_method_id'       => $row['PAYMENT_METHOD_ID']       ?? null,
                'payment_method_name'     => $row['PAYMENT_METHOD_NAME']     ?? null,
                'payment_type_id'         => $row['PAYMENT_TYPE_ID']         ?? null,
                'payment_type_name'       => $row['PAYMENT_TYPE_NAME']       ?? null,
                'user_creation_id'        => $row['USER_CREATION']           ?? null,
                'user_creation_full_name' => $row['USER_CREATION_FULL_NAME'] ?? null,
                'user_update_id'          => $row['USER_UPDATE']             ?? null,
                'user_update_full_name'   => $row['USER_UPDATE_FULL_NAME']   ?? null,
                'date_creation'           => $row['DATE_CREATION']           ?? null,
                'date_update'             => $row['DATE_UPDATE']             ?? null,
                'date_creation_date'      => isset($row['DATE_CREATION']) && $row['DATE_CREATION']
                    ? Carbon::parse($row['DATE_CREATION'])->toDateString()
                    : null,
                'payload'                 => $row,
                'payload_hash'            => $hash,
            ]
        );
    }
}
