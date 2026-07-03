<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds mode-aware summary fields to presence_payment_summaries so period /
 * hourly imports can store their own frozen totals without disturbing the
 * legacy weekly summary columns.
 *
 * SAFETY: all new columns nullable / defaulted; nothing existing is altered.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence_payment_summaries', function (Blueprint $table) {
            // Mirror the import's payment_mode for fast filtering / display
            $table->string('payment_mode', 20)->default('weekly')->after('presence_import_id');

            // Period mode: frozen params + tier bucket counts
            $table->decimal('period_unit_amount', 10, 2)->nullable()->after('weekly_unit_amount');
            $table->unsignedInteger('count_tier_full')->nullable()->after('period_unit_amount');
            $table->unsignedInteger('count_tier_partial')->nullable()->after('count_tier_full');
            $table->unsignedInteger('count_tier_zero')->nullable()->after('count_tier_partial');

            // Hourly mode: frozen final total (also stored on the import itself)
            $table->decimal('hourly_final_total', 12, 2)->nullable()->after('count_tier_zero');
        });
    }

    public function down(): void
    {
        Schema::table('presence_payment_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'payment_mode',
                'period_unit_amount',
                'count_tier_full',
                'count_tier_partial',
                'count_tier_zero',
                'hourly_final_total',
            ]);
        });
    }
};
