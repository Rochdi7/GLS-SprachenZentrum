<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_expenses', function (Blueprint $table) {
            // Unique ID from the CRM API — used for upsert deduplication on sync
            $table->string('crm_expense_id', 64)->nullable()->unique()->after('id');
            $table->string('crm_source', 20)->nullable()->after('crm_expense_id')
                  ->comment('API source: wimschool');
        });
    }

    public function down(): void
    {
        Schema::table('site_expenses', function (Blueprint $table) {
            $table->dropUnique(['crm_expense_id']);
            $table->dropColumn(['crm_expense_id', 'crm_source']);
        });
    }
};
