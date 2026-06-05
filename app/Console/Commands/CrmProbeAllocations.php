<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\Crm\Crm;
use Illuminate\Console\Command;

/**
 * One-shot diagnostic to see what /payment-allocations returns for a given
 * centre + range. Bypasses the GroupEvolution caching layers so we can tell
 * whether the upstream API has data at all.
 *
 * Usage:
 *   php artisan crm:probe-allocations Marrakech 2025-10-01 2026-05-31
 *   php artisan crm:probe-allocations --store=42 2025-10-01 2026-05-31
 */
class CrmProbeAllocations extends Command
{
    protected $signature = 'crm:probe-allocations
        {centre : Site name fragment (e.g. Marrakech) or "-" to use --store}
        {start : Start date yyyy-mm-dd}
        {end : End date yyyy-mm-dd}
        {--store= : Explicit strStoreId (used when centre is "-")}
        {--payments : Also probe /payments to compare}';

    protected $description = 'Probe the Wimschool /payment-allocations endpoint and show raw counts + sample rows';

    public function handle(Crm $crm): int
    {
        $centre = $this->argument('centre');
        $start  = $this->argument('start');
        $end    = $this->argument('end');
        $store  = $this->option('store') ? (int) $this->option('store') : null;

        // If centre is "-", expect --store. Otherwise resolve store from name.
        if ($centre !== '-' && $centre !== '') {
            $site = Site::whereNotNull('crm_store_id')
                ->where('name', 'like', "%{$centre}%")
                ->orderBy('name')
                ->first();
            if (!$site) {
                $this->error("No site matched '{$centre}' (need a site row with crm_store_id set).");
                return self::FAILURE;
            }
            $store = (int) $site->crm_store_id;
            $this->line("Matched site: <info>{$site->name}</info> → strStoreId={$store}");

            // Use the per-centre token if set
            if (!empty($site->crm_api_token)) {
                $crm = $crm->withToken($site->crm_api_token);
                $this->line('Using per-centre token from sites table.');
            }
        } elseif ($store === null) {
            $this->error('When centre is "-", you must pass --store=NNN.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->probe(
            label: 'payment-allocations (first page, size=5)',
            run: fn () => $crm->payments()->allocations(
                page: 0, size: 5, includeTotal: true,
                strStoreId: $store, startDate: $start, endDate: $end,
            ),
        );

        if ($this->option('payments')) {
            $this->newLine();
            $this->probe(
                label: 'payments (first page, size=5)',
                run: fn () => $crm->payments()->list(
                    page: 0, size: 5, includeTotal: true,
                    strStoreId: $store, startDate: $start, endDate: $end,
                ),
            );
        }

        return self::SUCCESS;
    }

    protected function probe(string $label, \Closure $run): void
    {
        $this->line("─── {$label} ───");
        try {
            $resp = $run();
        } catch (\Throwable $e) {
            $this->error('Exception: ' . $e->getMessage());
            return;
        }

        $pagination = $resp['pagination'] ?? [];
        $data       = $resp['data'] ?? [];

        $this->line('pagination.totalElements: <comment>' . ($pagination['totalElements'] ?? '—') . '</comment>');
        $this->line('pagination.totalPages:    <comment>' . ($pagination['totalPages']    ?? '—') . '</comment>');
        $this->line('pagination.hasNext:       <comment>' . var_export($pagination['hasNext'] ?? null, true) . '</comment>');
        $this->line('rows returned:            <comment>' . count($data) . '</comment>');

        if (!empty($data)) {
            $this->line('first row keys:');
            $first = $data[0];
            $this->line('  ' . implode(', ', array_keys($first)));
            $this->line('first row STUDENT_ID/CLASS_ID/SERVICE_TYPE_NAME/EFFECTIVE_DATE_PAYMENT:');
            $this->line(sprintf(
                '  %s / %s / %s / %s',
                $first['STUDENT_ID']            ?? 'null',
                $first['CLASS_ID']              ?? 'null',
                $first['SERVICE_TYPE_NAME']     ?? 'null',
                $first['EFFECTIVE_DATE_PAYMENT'] ?? $first['EFFECTIVE_DATE_PAYMENT_ALLOCATION'] ?? 'null',
            ));
        } else {
            $this->warn('No rows returned. Possible causes: token scope missing, no data in range, or API filter mismatch.');
        }
    }
}
