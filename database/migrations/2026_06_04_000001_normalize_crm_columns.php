<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds normalized (non-JSON) columns to existing CRM mirror tables so that
 * WHERE / GROUP BY clauses can use B-tree indexes instead of JSON_EXTRACT.
 *
 * Why: JSON_EXTRACT in a WHERE clause forces a full table scan.
 * With 50k+ attendance rows this causes 200ms–2s queries per dashboard request.
 *
 * After running this migration, execute the one-time backfill:
 *   php artisan crm:backfill-columns
 *
 * Going forward the sync commands populate these columns on every upsert.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── crm_attendance ──────────────────────────────────────────────────
        Schema::table('crm_attendance', function (Blueprint $table) {
            // NULL  → session exists in DB but was never "saisied" (draft)
            // NOT NULL → session was formally recorded with a creation timestamp
            $table->datetime('date_creation')->nullable()->after('raw_data');

            // Stored separately so groupDetails() can filter by status without JSON_EXTRACT
            $table->string('session_reference', 64)->nullable()->after('date_creation');
        });

        // Index date_creation: used in WHERE date_creation IS NULL / IS NOT NULL
        // and in GROUP BY crm_class_id, date with a date_creation filter
        Schema::table('crm_attendance', function (Blueprint $table) {
            $table->index('date_creation', 'idx_att_date_creation');
            // Composite: class + date — the most common query pattern in PresenceSuiviService
            $table->index(['crm_class_id', 'date'], 'idx_att_class_date');
        });

        // ── crm_registrations ───────────────────────────────────────────────
        Schema::table('crm_registrations', function (Blueprint $table) {
            // DATE(JSON_EXTRACT(raw_data,'$.DATE_CREATION')) → this column
            $table->date('date_creation')->nullable()->after('raw_data');

            // JSON_EXTRACT(raw_data,'$.REGISTRATION_STATUS_NAME') → this column
            $table->string('status_label', 64)->nullable()->after('date_creation');

            // Foreign key to crm_store — was missing, needed for per-center queries
            $table->unsignedInteger('crm_store_id')->nullable()->after('status_label');
        });

        Schema::table('crm_registrations', function (Blueprint $table) {
            $table->index('date_creation', 'idx_reg_date_creation');
            $table->index(['crm_store_id', 'date_creation'], 'idx_reg_store_date');
            $table->index(['crm_store_id', 'status_label'], 'idx_reg_store_status');
        });

        // ── crm_payment_snapshots ───────────────────────────────────────────
        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            // DATE(date_creation) extracted as a plain date column for indexed lookups
            // StatsController::periodComparison() and DailyReportService use this
            $table->date('date_creation_date')->nullable()->after('date_creation');
        });

        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            $table->index('date_creation_date', 'idx_snap_date_creation_date');
            $table->index(['crm_store_id', 'date_creation_date'], 'idx_snap_store_date_creation');
            $table->index(['payment_type_id', 'date_creation_date'], 'idx_snap_type_date_creation');
        });
    }

    public function down(): void
    {
        Schema::table('crm_attendance', function (Blueprint $table) {
            $table->dropIndex('idx_att_date_creation');
            $table->dropIndex('idx_att_class_date');
            $table->dropColumn(['date_creation', 'session_reference']);
        });

        Schema::table('crm_registrations', function (Blueprint $table) {
            $table->dropIndex('idx_reg_date_creation');
            $table->dropIndex('idx_reg_store_date');
            $table->dropIndex('idx_reg_store_status');
            $table->dropColumn(['date_creation', 'status_label', 'crm_store_id']);
        });

        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            $table->dropIndex('idx_snap_date_creation_date');
            $table->dropIndex('idx_snap_store_date_creation');
            $table->dropIndex('idx_snap_type_date_creation');
            $table->dropColumn('date_creation_date');
        });
    }
};
