<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the editable global default for period-mode payment tiers.
 *
 * This is ONLY the default used to snapshot new imports. Each import freezes
 * its own copy in presence_imports.period_tiers_json at creation time, so
 * editing this default never changes historical payroll.
 *
 * Tier semantics (per student, based on total presences across the period):
 *   - weeks = how many "week equivalents" of base_price/4 to pay
 *   - weeks = "full" pays the entire base_price
 *   - anything below the lowest tier's min pays 0
 */
return new class extends Migration
{
    public function up(): void
    {
        $tiers = json_encode([
            ['min' => 5,  'max' => 6,    'weeks' => 1],
            ['min' => 7,  'max' => 10,   'weeks' => 2],
            ['min' => 11, 'max' => null, 'weeks' => 'full'],
        ]);

        // Idempotent: don't duplicate if re-run
        if (! DB::table('system_configs')->where('key', 'period.tiers')->exists()) {
            DB::table('system_configs')->insert([
                'key'         => 'period.tiers',
                'value'       => $tiers,
                'type'        => 'string', // stored as raw JSON string, decoded by the service
                'description' => 'Paliers de paiement par période (présences → semaines équivalentes). JSON figé par import à la création.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Number of weeks a full base_price is divided into (base_price / weeks_per_period)
        if (! DB::table('system_configs')->where('key', 'period.weeks_per_period')->exists()) {
            DB::table('system_configs')->insert([
                'key'         => 'period.weeks_per_period',
                'value'       => '4',
                'type'        => 'integer',
                'description' => 'Nombre de semaines par période (base_price ÷ ce nombre = 1 semaine équivalente).',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('system_configs')
            ->whereIn('key', ['period.tiers', 'period.weeks_per_period'])
            ->delete();
    }
};
