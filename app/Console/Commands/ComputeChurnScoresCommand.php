<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\Crm\Stats\ChurnScoringService;
use Illuminate\Console\Command;

class ComputeChurnScoresCommand extends Command
{
    protected $signature = 'crm:churn-scores
        {--store=* : Store IDs to process}
        {--all : Process all stores}';

    protected $description = 'Compute student churn risk scores from CRM data';

    public function handle(ChurnScoringService $service): int
    {
        $storeIds = $this->option('store');
        $all      = $this->option('all');

        if (empty($storeIds) && !$all) {
            $this->error('Specify --all or one or more --store=ID options.');
            return self::FAILURE;
        }

        if ($all) {
            $sites = Site::whereNotNull('crm_store_id')->get();
        } else {
            $sites = Site::whereNotNull('crm_store_id')
                ->whereIn('crm_store_id', array_map('intval', $storeIds))
                ->get();
        }

        if ($sites->isEmpty()) {
            $this->warn('No sites with crm_store_id found.');
            return self::SUCCESS;
        }

        foreach ($sites as $site) {
            $this->info("Processing store #{$site->crm_store_id} ({$site->name})...");
            try {
                $count = $service->computeForStore((int) $site->crm_store_id);
                $this->info("  => {$count} students scored.");
            } catch (\Throwable $e) {
                $this->error("  => Failed: {$e->getMessage()}");
            }
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
