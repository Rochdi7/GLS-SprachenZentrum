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
        // Every column/index addition is wrapped defensively.
        // Reason: this migration may be applied to servers where some columns
        // already exist from earlier ad-hoc migrations or partial runs.
        // try/catch on indexes, Schema::hasColumn() on columns — both patterns
        // are safe to re-run and idempotent.

        // ── crm_attendance ──────────────────────────────────────────────────
        Schema::table('crm_attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_attendance', 'date_creation')) {
                $table->datetime('date_creation')->nullable()->after('raw_data');
            }
            if (!Schema::hasColumn('crm_attendance', 'session_reference')) {
                $table->string('session_reference', 64)->nullable()->after('date_creation');
            }
        });

        try {
            Schema::table('crm_attendance', function (Blueprint $table) {
                $table->index('date_creation', 'idx_att_date_creation');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('crm_attendance', function (Blueprint $table) {
                $table->index(['crm_class_id', 'date'], 'idx_att_class_date');
            });
        } catch (\Throwable) {}

        // ── crm_registrations ───────────────────────────────────────────────
        Schema::table('crm_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_registrations', 'date_creation')) {
                $table->date('date_creation')->nullable()->after('raw_data');
            }
            if (!Schema::hasColumn('crm_registrations', 'status_label')) {
                $table->string('status_label', 64)->nullable()->after('date_creation');
            }
            // crm_store_id was added by 2026_06_02_210000 — skip if already exists
            if (!Schema::hasColumn('crm_registrations', 'crm_store_id')) {
                $table->unsignedInteger('crm_store_id')->nullable()->after('status_label');
            }
        });

        try {
            Schema::table('crm_registrations', function (Blueprint $table) {
                $table->index('date_creation', 'idx_reg_date_creation');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('crm_registrations', function (Blueprint $table) {
                $table->index(['crm_store_id', 'date_creation'], 'idx_reg_store_date');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('crm_registrations', function (Blueprint $table) {
                $table->index(['crm_store_id', 'status_label'], 'idx_reg_store_status');
            });
        } catch (\Throwable) {}

        // ── crm_payment_snapshots ───────────────────────────────────────────
        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_payment_snapshots', 'date_creation_date')) {
                $table->date('date_creation_date')->nullable()->after('date_creation');
            }
        });

        try {
            Schema::table('crm_payment_snapshots', function (Blueprint $table) {
                $table->index('date_creation_date', 'idx_snap_date_creation_date');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('crm_payment_snapshots', function (Blueprint $table) {
                $table->index(['crm_store_id', 'date_creation_date'], 'idx_snap_store_date_creation');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('crm_payment_snapshots', function (Blueprint $table) {
                $table->index(['payment_type_id', 'date_creation_date'], 'idx_snap_type_date_creation');
            });
        } catch (\Throwable) {}
    }

    public function down(): void
    {
        // Drop indexes first (MySQL requires this before dropping columns),
        // wrapped in try/catch in case they were never created
        foreach (['idx_att_date_creation', 'idx_att_class_date'] as $idx) {
            try { Schema::table('crm_attendance', fn ($t) => $t->dropIndex($idx)); } catch (\Throwable) {}
        }
        foreach (['date_creation', 'session_reference'] as $col) {
            if (Schema::hasColumn('crm_attendance', $col)) {
                Schema::table('crm_attendance', fn ($t) => $t->dropColumn($col));
            }
        }

        foreach (['idx_reg_date_creation', 'idx_reg_store_date', 'idx_reg_store_status'] as $idx) {
            try { Schema::table('crm_registrations', fn ($t) => $t->dropIndex($idx)); } catch (\Throwable) {}
        }
        // Only drop crm_store_id if this migration added it (it may belong to the 210000 migration)
        foreach (['date_creation', 'status_label'] as $col) {
            if (Schema::hasColumn('crm_registrations', $col)) {
                Schema::table('crm_registrations', fn ($t) => $t->dropColumn($col));
            }
        }

        foreach (['idx_snap_date_creation_date', 'idx_snap_store_date_creation', 'idx_snap_type_date_creation'] as $idx) {
            try { Schema::table('crm_payment_snapshots', fn ($t) => $t->dropIndex($idx)); } catch (\Throwable) {}
        }
        if (Schema::hasColumn('crm_payment_snapshots', 'date_creation_date')) {
            Schema::table('crm_payment_snapshots', fn ($t) => $t->dropColumn('date_creation_date'));
        }
    }
};
