<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Precomputed group evolution buckets per class per date range.
 *
 * WHY THIS TABLE EXISTS:
 * GroupEvolutionService::build() previously called /payment-allocations up to
 * 40 times per request, then ran PHP loops to compute debuts/ajouts/quittants.
 * Dashboard load time: 5–300 seconds. PHP timeout: frequent.
 *
 * Now BuildGroupEvolutionCommand (crm:build-group-evolution) computes all
 * five buckets from local tables during the scheduled sync and writes here.
 * The dashboard reads one SELECT < 80ms.
 *
 * Populated by: php artisan crm:build-group-evolution --all
 * Read by:      GroupEvolutionService::build()
 *
 * The five buckets (French terms match the UI):
 *   debuts      — students whose first payment month = class START_DATE month
 *   ajouts      — students who joined after the class started
 *   quittants   — students who stopped paying (not a transfer)
 *   changements — students who transferred to a different group
 *   actifs      — CLASS_COUNT_STUDENTS_ACTIVE from the API (stored during class mirror)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_group_evolution_snapshot', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('crm_store_id')->index();
            $table->unsignedBigInteger('class_id');
            $table->string('class_name', 191);
            $table->date('class_start_date')->nullable();
            $table->date('class_end_date')->nullable();

            // Pre-extracted start month ('YYYY-MM') — avoids Carbon::parse() per row
            $table->char('class_start_month', 7)->nullable();

            // The five evolution buckets
            $table->unsignedSmallInteger('debuts')->default(0);
            $table->unsignedSmallInteger('ajouts')->default(0);
            $table->unsignedSmallInteger('quittants')->default(0);
            $table->unsignedSmallInteger('changements')->default(0);
            $table->unsignedSmallInteger('actifs')->default(0);

            // The payment date range this snapshot covers
            // Dashboard queries filter by (crm_store_id, range_start <= startDate, range_end >= endDate)
            $table->date('range_start');
            $table->date('range_end');

            $table->timestamp('computed_at');
            $table->timestamps();

            // Unique constraint prevents duplicate snapshots for the same class+range
            $table->unique(
                ['crm_store_id', 'class_id', 'range_start', 'range_end'],
                'uniq_evo_snapshot'
            );

            // Index for the dashboard query: WHERE crm_store_id = ? AND range_start <= ? AND range_end >= ?
            $table->index(
                ['crm_store_id', 'range_start', 'range_end'],
                'idx_evo_store_range'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_group_evolution_snapshot');
    }
};
