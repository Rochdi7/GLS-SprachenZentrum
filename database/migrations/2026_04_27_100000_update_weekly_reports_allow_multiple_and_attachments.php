<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropUnique(['teacher_id', 'report_date']);
        });

        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->index(['teacher_id', 'report_date']);
            $table->string('attachment_path')->nullable()->after('notes');
            $table->string('attachment_original_name')->nullable()->after('attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropIndex(['teacher_id', 'report_date']);
            $table->dropColumn(['attachment_path', 'attachment_original_name']);
        });

        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->unique(['teacher_id', 'report_date']);
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
        });
    }
};
