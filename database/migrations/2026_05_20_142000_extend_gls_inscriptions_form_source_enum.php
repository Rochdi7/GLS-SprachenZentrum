<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Widen the form_source enum to support ad-landing-page tracking
        DB::statement("ALTER TABLE gls_inscriptions MODIFY COLUMN form_source ENUM('modal','page','meta_ads','google_ads','unknown') NOT NULL DEFAULT 'unknown'");
    }

    public function down(): void
    {
        // Revert any new values back to 'unknown' before shrinking the enum
        DB::table('gls_inscriptions')
            ->whereIn('form_source', ['meta_ads', 'google_ads'])
            ->update(['form_source' => 'unknown']);

        DB::statement("ALTER TABLE gls_inscriptions MODIFY COLUMN form_source ENUM('modal','page','unknown') NOT NULL DEFAULT 'unknown'");
    }
};
