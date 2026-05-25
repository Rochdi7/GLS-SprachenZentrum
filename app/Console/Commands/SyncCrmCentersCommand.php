<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;

class SyncCrmCentersCommand extends Command
{
    protected $signature = 'crm:sync-centers';
    protected $description = 'Populate crm_store_id in the sites table based on names';

    public function handle()
    {
        $map = [
            'Rabat'      => 2,
            'Marrakech'  => 1,
            'Kenitra'    => 4,
            'Agadir'     => 5,
            'Casablanca' => 6,
            'Salé'       => 7,
            'Online'     => 0,
        ];

        $this->info("Starting CRM center sync...");

        foreach ($map as $cityName => $storeId) {
            $site = Site::where('name', 'like', '%' . $cityName . '%')
                ->orWhere('city', 'like', '%' . $cityName . '%')
                ->first();

            if ($site) {
                $site->update(['crm_store_id' => $storeId]);
                $this->info("Updated {$site->name} (ID: {$site->id}) with crm_store_id: {$storeId}");
            } else {
                $this->warn("Could not find site matching: {$cityName}");
            }
        }

        $this->info("Sync complete!");
    }
}
