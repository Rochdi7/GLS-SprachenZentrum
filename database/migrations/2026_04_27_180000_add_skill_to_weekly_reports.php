<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            // Skill: lesen, hoeren, grammatik, schreiben, sprechen, or null for free-text/single-group notes
            $table->string('skill', 20)->nullable()->after('group_id');
            $table->index(['teacher_id', 'group_id', 'report_date', 'skill'], 'wr_teacher_group_date_skill_idx');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropIndex('wr_teacher_group_date_skill_idx');
            $table->dropColumn('skill');
        });
    }
};
