<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('group_id')
                ->constrained('sites')->nullOnDelete();
        });

        // Backfill from existing group → site link.
        DB::statement('
            UPDATE attestations a
            INNER JOIN groups g ON g.id = a.group_id
            SET a.site_id = g.site_id
            WHERE a.site_id IS NULL
        ');

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('certificate_type')
                ->constrained('sites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });
    }
};
