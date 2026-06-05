<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the Wimschool CRM mapping (strStoreId + per-center token) onto the
 * sites table from the spreadsheet shared by the school.
 *
 * Idempotent: re-running only refreshes crm_store_id / crm_token.
 * Sites are matched by case-insensitive name; missing ones are created as
 * inactive so the admin can fill in their details later.
 */
class CrmSiteTokensSeeder extends Seeder
{
    public function run(): void
    {
        $token = 'hsk_live_96eb7961aa631fc8d86e4d7e768dd7266ed02d8b73a45de82033d334e4d8ceb7';

        $centers = [
            ['name' => 'GLS Marrakech',  'city' => 'Marrakech',  'crm_store_id' => 50970],
            ['name' => 'GLS Sale',       'city' => 'Sale',       'crm_store_id' => 50995],
            ['name' => 'GLS Casablanca', 'city' => 'Casablanca', 'crm_store_id' => 50996],
            ['name' => 'GLS Kenitra',    'city' => 'Kenitra',    'crm_store_id' => 50997],
            ['name' => 'GLS Agadir',     'city' => 'Agadir',     'crm_store_id' => 50999],
            ['name' => 'GLS Rabat',      'city' => 'Rabat',      'crm_store_id' => 51000],
            ['name' => 'GLS Online',     'city' => 'Online',     'crm_store_id' => 51151],
        ];

        foreach ($centers as $c) {
            $site = Site::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($c['name'])])
                ->first();

            if ($site) {
                $site->update([
                    'crm_store_id' => $c['crm_store_id'],
                    'crm_token'    => $token,
                ]);
                $this->command?->info("Updated {$c['name']} → store {$c['crm_store_id']}");
            } else {
                Site::create([
                    'name'         => $c['name'],
                    'slug'         => Str::slug($c['name']),
                    'city'         => $c['city'],
                    'is_active'    => false,
                    'crm_store_id' => $c['crm_store_id'],
                    'crm_token'    => $token,
                ]);
                $this->command?->info("Created {$c['name']} (inactive) → store {$c['crm_store_id']}");
            }
        }
    }
}
