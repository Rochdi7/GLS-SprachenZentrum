<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\Crm\Stats\AlertsService;
use Illuminate\Console\Command;

class CrmGenerateAlertsCommand extends Command
{
    protected $signature = 'crm:generate-alerts
        {--store=*  : Only generate for specific CRM store IDs (crm_store_id)}
        {--type=*   : Only run specific detectors: absent_student, unpaid_30d, cheque_due_soon, weak_attendance, group_near_end}
        {--dry-run  : Show detection counts without writing to DB}
        {--prune    : Delete resolved/dismissed alerts older than 30 days before generating}';

    protected $description = 'Detect CRM business alerts (absences, unpaid, cheques, attendance) and upsert into crm_alerts';

    public function handle(AlertsService $svc): int
    {
        $dryRun   = (bool) $this->option('dry-run');
        $storeIds = array_filter(array_map('intval', (array) $this->option('store')));

        if ($dryRun) {
            $this->warn('[DRY-RUN] No data will be written to crm_alerts.');
        }

        // Prune stale resolved/dismissed alerts first
        if ($this->option('prune') && !$dryRun) {
            $pruned = $svc->prune(30);
            $this->line("  🗑  Pruned {$pruned} old resolved/dismissed alert(s).");
        }

        // Resolve which stores to process
        if (empty($storeIds)) {
            $storeIds = Site::whereNotNull('crm_store_id')
                ->pluck('crm_store_id')
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            // null = ALL_STORES token — also run once for global data
            $storeIds[] = null;
        }

        $totalGenerated = 0;
        $totalSkipped   = 0;

        foreach ($storeIds as $storeId) {
            $label = $storeId ? "store #{$storeId}" : 'all stores';
            $this->info("┌─ Generating alerts for {$label}");

            $result = $svc->generate($storeId, $dryRun);

            $this->line('  Types detected:');
            foreach ($result['types'] as $type => $count) {
                $icon = $count > 0 ? '<fg=yellow>⚠</>' : '<fg=gray>·</>';
                $this->line("    {$icon}  {$type}: {$count}");
            }

            if (!$dryRun) {
                $this->line("  ✓ Generated: {$result['generated']}  |  Updated/skipped: {$result['skipped']}");
                $totalGenerated += $result['generated'];
                $totalSkipped   += $result['skipped'];
            }

            $this->info("└─ done");
            $this->line('');
        }

        if (!$dryRun) {
            $this->info("═══ Total: {$totalGenerated} new alert(s) created, {$totalSkipped} updated/skipped ═══");
        }

        return self::SUCCESS;
    }
}
