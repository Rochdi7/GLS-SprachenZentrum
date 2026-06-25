<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_sync_log', function (Blueprint $table) {
            $table->unsignedInteger('attempts')->default(0)->change();
        });

        // Reset overflowed counter so sync can proceed immediately.
        DB::table('crm_sync_log')->update(['attempts' => 0]);
    }

    public function down(): void
    {
        Schema::table('crm_sync_log', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0)->change();
        });
    }
};
