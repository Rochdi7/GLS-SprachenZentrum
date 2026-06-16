<?php

namespace App\Console\Commands;

use App\Services\Crm\Stats\GroupProfitabilityService;
use Illuminate\Console\Command;

class CrmBuildGroupProfitabilityCommand extends Command
{
    protected $signature = 'crm:build-group-profitability
        {--months=3   : How many months back to compute (default: 3)}
        {--store=*    : Restrict to specific CRM store IDs}
        {--month=     : Compute for a single specific month (YYYY-MM)}
        {--dry-run    : Print computed row count without writing to DB}';

    protected $description = 'Compute per-group revenue, teacher salary, profit, margin and attendance rate into crm_group_profitability';

    public function handle(GroupProfitabilityService $svc): int
    {
        $dryRun      = (bool) $this->option('dry-run');
        $storeIds    = array_filter(array_map('intval', (array) $this->option('store')));
        $months      = max(1, (int) $this->option('months'));
        $singleMonth = $this->option('month') ?: null;

        if ($dryRun) {
            $this->warn('[DRY-RUN] No data will be written to crm_group_profitability.');
        }

        $storeList = empty($storeIds) ? [null] : $storeIds;

        $totalRows = 0;

        foreach ($storeList as $storeId) {
            $label = $storeId ? "store #{$storeId}" : 'all stores';
            $this->info("┌─ Building profitability for {$label}");

            if (!$dryRun) {
                $result    = $svc->build($months, $storeId, $singleMonth);
                $totalRows += $result['rows_written'];

                $this->line('  Months processed: ' . implode(', ', $result['months_processed']));
                $this->line("  Rows written: {$result['rows_written']}");
            } else {
                $this->line("  [DRY] Would process " . ($singleMonth ?: "{$months} months") . " for {$label}");
            }

            $this->info("└─ done");
            $this->line('');
        }

        if (!$dryRun) {
            $this->info("═══ Total rows written: {$totalRows} ═══");
        }

        return self::SUCCESS;
    }
}
