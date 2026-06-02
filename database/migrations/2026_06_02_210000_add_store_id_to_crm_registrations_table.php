<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_registrations', function (Blueprint $table) {
            $table->integer('crm_store_id')->nullable()->index()->after('crm_class_id');
        });
    }

    public function down(): void
    {
        Schema::table('crm_registrations', function (Blueprint $table) {
            $table->dropColumn('crm_store_id');
        });
    }
};
