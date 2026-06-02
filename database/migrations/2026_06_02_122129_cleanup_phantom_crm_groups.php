<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove phantom groups that were auto-created by the CRM API controller.
     * Phantom groups have crm_class_id set but no site_id and no time_range,
     * meaning they were never created through the normal group CRUD form.
     */
    public function up(): void
    {
        DB::table('groups')
            ->whereNotNull('crm_class_id')
            ->whereNull('site_id')
            ->whereNull('time_range')
            ->delete();
    }

    public function down(): void
    {
        // Non-reversible — phantom rows had no real data
    }
};
