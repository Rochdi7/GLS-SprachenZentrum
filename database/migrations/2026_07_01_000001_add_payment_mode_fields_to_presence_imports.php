<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds period / hourly payment-mode support to presence_imports.
 *
 * SAFETY:
 *  - Every new column is nullable OR carries a default so existing rows stay valid.
 *  - `payment_mode` defaults to 'weekly' → all existing imports become 'weekly'
 *    automatically via the column default (no UPDATE, no recalculation).
 *  - No enums (string + Laravel validation) to avoid ALTER-TABLE deployment risk.
 *  - No renames, no drops, no changes to existing columns.
 *  - down() only drops the columns this migration added.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            // Mode dispatch — 'weekly' (legacy), 'period', 'hourly'
            $table->string('payment_mode', 20)->default('weekly')->after('is_crm_api');

            // Lifecycle — 'draft', 'validated', 'paid', 'locked'
            $table->string('status', 20)->default('draft')->after('payment_mode');

            // Reproducibility: which calc engine version produced this import
            $table->unsignedInteger('calculation_version')->default(1)->after('status');

            /* ---- Period mode ---------------------------------------------- */
            // Calendar month/year this import is attached to (may differ from date_start)
            $table->unsignedTinyInteger('attached_month')->nullable()->after('calculation_version');
            $table->unsignedSmallInteger('attached_year')->nullable()->after('attached_month');
            // Which month of the group's lifecycle this is (Dec can be Month 1)
            $table->unsignedSmallInteger('group_month_number')->nullable()->after('attached_year');
            // Frozen base price per student for period mode (snapshot, reproducible)
            $table->decimal('base_price', 10, 2)->nullable()->after('group_month_number');
            // Frozen tier config snapshot copied from system config at creation time
            $table->json('period_tiers_json')->nullable()->after('base_price');

            /* ---- Hourly mode ---------------------------------------------- */
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('period_tiers_json');
            $table->decimal('total_hours', 8, 2)->nullable()->after('hourly_rate');
            $table->decimal('performance_bonus', 10, 2)->nullable()->after('total_hours');
            $table->decimal('final_total', 12, 2)->nullable()->after('performance_bonus');
        });
    }

    public function down(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            $table->dropColumn([
                'payment_mode',
                'status',
                'calculation_version',
                'attached_month',
                'attached_year',
                'group_month_number',
                'base_price',
                'period_tiers_json',
                'hourly_rate',
                'total_hours',
                'performance_bonus',
                'final_total',
            ]);
        });
    }
};
