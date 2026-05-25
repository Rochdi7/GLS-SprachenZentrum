<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'crm_store_id')) {
                $table->integer('crm_store_id')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('sites', 'crm_token')) {
                $table->text('crm_token')->nullable()->after('crm_store_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['crm_store_id', 'crm_token']);
        });
    }
};
