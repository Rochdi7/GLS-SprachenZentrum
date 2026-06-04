<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds due-date (date échéance) and rest_amount to payment snapshots.
 *
 * Populated during crm:snapshot-payments by cross-referencing
 * payment-allocations (PAYMENT_ID → REGISTRATION_ID) against
 * crm_collection_rows (REGISTRATION_ID → DUE_DATE / REST_AMOUNT).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_payment_snapshots', 'registration_id')) {
                $table->unsignedBigInteger('registration_id')->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('crm_payment_snapshots', 'due_date')) {
                $table->date('due_date')->nullable()->after('effective_date');
            }
            if (!Schema::hasColumn('crm_payment_snapshots', 'rest_amount')) {
                $table->decimal('rest_amount', 14, 2)->nullable()->after('amount');
            }
        });

        try {
            Schema::table('crm_payment_snapshots', function (Blueprint $table) {
                $table->index('due_date', 'idx_snap_due_date');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('crm_payment_snapshots', function (Blueprint $table) {
                $table->index(['crm_store_id', 'due_date'], 'idx_snap_store_due_date');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('crm_payment_snapshots', function (Blueprint $table) {
                $table->index('registration_id', 'idx_snap_registration_id');
            });
        } catch (\Throwable) {}
    }

    public function down(): void
    {
        foreach (['idx_snap_due_date', 'idx_snap_store_due_date', 'idx_snap_registration_id'] as $idx) {
            try {
                Schema::table('crm_payment_snapshots', fn ($t) => $t->dropIndex($idx));
            } catch (\Throwable) {}
        }

        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            foreach (['registration_id', 'due_date', 'rest_amount'] as $col) {
                if (Schema::hasColumn('crm_payment_snapshots', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
