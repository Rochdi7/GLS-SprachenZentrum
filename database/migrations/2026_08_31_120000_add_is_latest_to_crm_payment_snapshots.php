<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marks the most recent snapshot row of every payment with is_latest = 1.
 *
 * WHY:
 * crm_payment_snapshots is append-only — one row per payment per night — so
 * "the current state of a payment" means "its row with the highest
 * snapshot_date". Expressed as a query that is either a correlated subquery
 * (MariaDB: DEPENDENT SUBQUERY, re-runs the MAX() per candidate row) or a
 * grouped derived table (MariaDB: LATERAL DERIVED, re-evaluated per row).
 * Both make cost grow with snapshot HISTORY rather than with the number of
 * payments, so every night of syncing made the Vue 360 page slower — which is
 * what eventually produced the nginx 504.
 *
 * A persisted flag turns that into an indexed equality lookup that stays flat
 * as history accumulates. CrmSnapshotPaymentsCommand maintains it after each
 * nightly run; this migration backfills the existing rows.
 *
 * Note it must be a per-payment maximum, not a global one: a payment deleted
 * in the CRM simply stops being snapshotted, so its latest row is an older
 * date than the newest sync. Detecting exactly that is the table's purpose.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_payment_snapshots', 'is_latest')) {
                $table->boolean('is_latest')->default(false)->after('snapshot_date');
            }
        });

        // Covers the hot access pattern: "current state of the payments of
        // these students/registrations", which is every Vue 360 query.
        foreach ([
            'idx_snap_latest' => ['is_latest'],
            'idx_snap_latest_student' => ['is_latest', 'student_id'],
            'idx_snap_latest_registration' => ['is_latest', 'registration_id'],
        ] as $name => $columns) {
            try {
                Schema::table('crm_payment_snapshots', fn (Blueprint $t) => $t->index($columns, $name));
            } catch (\Throwable) {
                // Index already present — nothing to do.
            }
        }

        $this->backfill();
    }

    /**
     * Set is_latest on the newest row of each payment.
     *
     * Done in one grouped pass rather than per payment: the join computes each
     * maximum once, which is exactly the cost profile the flag exists to buy.
     */
    private function backfill(): void
    {
        DB::table('crm_payment_snapshots')->update(['is_latest' => false]);

        DB::statement('
            UPDATE crm_payment_snapshots ps
            JOIN (
                SELECT crm_payment_id, MAX(snapshot_date) AS snapshot_date
                FROM crm_payment_snapshots
                GROUP BY crm_payment_id
            ) latest
              ON latest.crm_payment_id = ps.crm_payment_id
             AND latest.snapshot_date  = ps.snapshot_date
            SET ps.is_latest = 1
        ');
    }

    public function down(): void
    {
        foreach (['idx_snap_latest', 'idx_snap_latest_student', 'idx_snap_latest_registration'] as $name) {
            try {
                Schema::table('crm_payment_snapshots', fn (Blueprint $t) => $t->dropIndex($name));
            } catch (\Throwable) {
                // Index already absent.
            }
        }

        Schema::table('crm_payment_snapshots', function (Blueprint $table) {
            if (Schema::hasColumn('crm_payment_snapshots', 'is_latest')) {
                $table->dropColumn('is_latest');
            }
        });
    }
};
