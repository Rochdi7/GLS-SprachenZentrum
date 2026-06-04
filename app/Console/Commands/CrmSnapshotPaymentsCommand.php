<?php

namespace App\Console\Commands;

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
 * Run nightly via the scheduler. The diff page compares two consecutive days
 * to detect deletions / amount changes / late edits.
 */
class CrmSnapshotPaymentsCommand extends Command
{
    protected $signature = 'crm:snapshot-payments
        {--store=* : Store IDs to snapshot (repeatable, default: all configured centers)}
        {--date=   : Snapshot date (default: today, format YYYY-MM-DD)}';

    protected $description = 'Capture daily snapshot of all CRM payments for fraud detection.';

    public function handle(Crm $crm): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date'))->toDateString() : Carbon::today()->toDateString();
        $storeIds = array_map('intval', array_filter((array) $this->option('store')));

        $sites = Site::whereNotNull('crm_store_id')->where('crm_store_id', '>', 0)
            ->when(!empty($storeIds), fn ($q) => $q->whereIn('crm_store_id', $storeIds))
            ->get();

        if ($sites->isEmpty()) {
            $this->error('No sites with crm_store_id configured.');
            return self::FAILURE;
        }

        $this->info("Snapshotting payments for {$date} across {$sites->count()} center(s)...");

        $totalCaptured = 0;
        $totalErrors   = 0;

        foreach ($sites as $i => $site) {
            if ($i > 0) {
                $this->line("  (pause 90s between centers...)");
                sleep(90);
            }

            $this->line("  → {$site->name} (#{$site->crm_store_id})");

            try {
                $captured = $this->snapshotCenter($crm, $site, $date);
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

    protected function snapshotCenter(Crm $crm, Site $site, string $date): int
    {
        $page = 0;
        $captured = 0;

        do {
            if ($page > 0) {
                sleep(5);
            }

            $this->line("    Page {$page}...");

            $response = $crm->payments()->list(
                page: $page,
                size: 25,
                strStoreId: $site->crm_store_id,
            );

            $count = count($response['data'] ?? []);
            $this->line("    Page {$page} — {$count} rows");

            foreach ($response['data'] ?? [] as $row) {
                $this->upsertSnapshot($row, $date);
                $captured++;
            }

            $hasNext = $response['pagination']['hasNext']
                ?? $response['pagination']['hasMore']
                ?? false;
            $page++;
        } while ($hasNext && $page < 200); // 20k payments per center safety cap

        return $captured;
    }

    protected function upsertSnapshot(array $row, string $date): void
    {
        $hash = hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        CrmPaymentSnapshot::updateOrCreate(
            [
                'crm_payment_id' => $row['ID'],
                'snapshot_date'  => $date,
            ],
            [
                'crm_store_id'            => $row['STR_STORE_ID']             ?? null,
                'student_id'              => $row['STUDENT_ID']               ?? null,
                'reference'               => $row['REFERENCE']                ?? null,
                'amount'                  => $row['AMOUNT']                   ?? null,
                'effective_date'          => $row['EFFECTIVE_DATE']           ?? null,
                'payment_method_id'       => $row['PAYMENT_METHOD_ID']        ?? null,
                'payment_method_name'     => $row['PAYMENT_METHOD_NAME']      ?? null,
                'payment_type_id'         => $row['PAYMENT_TYPE_ID']          ?? null,
                'payment_type_name'       => $row['PAYMENT_TYPE_NAME']        ?? null,
                'user_creation_id'        => $row['USER_CREATION']            ?? null,
                'user_creation_full_name' => $row['USER_CREATION_FULL_NAME']  ?? null,
                'user_update_id'          => $row['USER_UPDATE']              ?? null,
                'user_update_full_name'   => $row['USER_UPDATE_FULL_NAME']    ?? null,
                'date_creation'           => $row['DATE_CREATION']            ?? null,
                'date_update'             => $row['DATE_UPDATE']              ?? null,
                // Normalized column — avoids JSON_EXTRACT in DailyReportService WHERE clauses
                'date_creation_date'      => isset($row['DATE_CREATION']) && $row['DATE_CREATION']
                    ? \Carbon\Carbon::parse($row['DATE_CREATION'])->toDateString()
                    : null,
                'payload'                 => $row,
                'payload_hash'            => $hash,
            ]
        );
    }
}
