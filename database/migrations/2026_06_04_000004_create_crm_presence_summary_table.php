<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Monthly per-class attendance aggregate table.
 *
 * WHY THIS TABLE EXISTS:
 * PresenceSuiviService::allTimeTotals() walked a CarbonPeriod from the earliest
 * attendance date to today (potentially 700+ days) × 30 classes = 21,000+ PHP
 * iterations per request. This caused 2–15 second page loads.
 *
 * BuildPresenceSummaryCommand (crm:build-presence-summary) runs a single SQL
 * GROUP BY aggregation and writes results here. The dashboard reads one SUM query.
 *
 * Populated by: php artisan crm:build-presence-summary --all
 * Read by:      PresenceSuiviService::allTimeTotals()
 *               PresenceSuiviService::buildMonth() (for fraud summary)
 *
 * Column notes:
 *   saisie_sessions  — sessions that have date_creation set (formally recorded)
 *   draft_sessions   — sessions in crm_attendance but without date_creation
 *   expected_sessions — saisie + draft (total accounted for)
 *   missing_sessions — DOW-inferred sessions with no attendance record at all
 *                      (computed by BuildPresenceSummaryCommand if enabled)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_presence_summary', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('crm_class_id')->index();
            $table->unsignedInteger('crm_store_id')->index();

            // Denormalized for display — avoids a JOIN on every dashboard read
            $table->string('class_name', 191);
            $table->string('teacher_name', 191)->nullable();

            // First day of the month this row covers: 2025-10-01
            $table->date('month');

            // Session counts
            $table->unsignedSmallInteger('saisie_sessions')->default(0);
            $table->unsignedSmallInteger('draft_sessions')->default(0);
            $table->unsignedSmallInteger('expected_sessions')->default(0);
            $table->unsignedSmallInteger('missing_sessions')->default(0);

            // Attendance totals across all saisie sessions this month
            $table->unsignedSmallInteger('total_present')->default(0);
            $table->unsignedSmallInteger('total_absent')->default(0);
            $table->unsignedSmallInteger('total_students')->default(0);

            $table->timestamp('computed_at');
            $table->timestamps();

            // One row per class per month
            $table->unique(['crm_class_id', 'month'], 'uniq_presence_summary');

            // Dashboard query: WHERE crm_store_id = ? (optional) — store-level totals
            $table->index(['crm_store_id', 'month'], 'idx_ps_store_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_presence_summary');
    }
};
