<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirror table for /api/external/v1/bulk/payment-allocations.
 *
 * WHY THIS TABLE EXISTS:
 * GroupEvolutionService previously called the payment-allocations API
 * live during dashboard requests (up to 40 pages × 25 rows = 1,000 HTTP round-trips).
 * This caused 5–300 second page loads and frequent PHP timeouts.
 *
 * This table stores payment allocation data locally so the dashboard reads
 * a simple SELECT instead of making any API calls.
 *
 * Populated by: php artisan crm:sync-payment-allocations --all
 * Read by:      BuildGroupEvolutionCommand, GroupEvolutionService (via snapshot)
 *
 * Key indexes explained:
 *   idx_pa_store_month   — GroupEvolution filters by (store, month range)
 *   idx_pa_class_month   — per-class month lookup when computing debuts/ajouts
 *   idx_pa_student_class — student-class pair lookup for transfer detection
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_payment_allocations', function (Blueprint $table) {
            $table->id();

            // API primary key — unique per allocation record
            $table->string('crm_id', 64)->unique();

            $table->unsignedInteger('crm_store_id')->index();

            // The student who made the payment
            $table->string('student_id', 64)->index();

            // The class/group the payment was allocated to
            $table->unsignedBigInteger('class_id')->index();

            // e.g. "Mensualité", "Inscription", "Frais d'inscription"
            $table->string('service_type_name', 128)->nullable();

            // Pre-computed flag so evolution builder avoids string comparison per row
            $table->boolean('is_inscription')->default(false)->index();

            // The effective date of the allocation (UTC → stored as Casablanca date)
            $table->date('allocation_date')->index();

            // Pre-extracted 'YYYY-MM' string — avoids DATE_FORMAT() in GROUP BY
            $table->char('allocation_month', 7)->index();

            // Original payment date (may differ from allocation date)
            $table->date('payment_date')->nullable();

            $table->decimal('amount', 10, 2)->nullable();

            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Composite indexes matching GroupEvolutionService query patterns
            $table->index(['crm_store_id', 'allocation_month'], 'idx_pa_store_month');
            $table->index(['class_id', 'allocation_month'], 'idx_pa_class_month');
            $table->index(['student_id', 'class_id'], 'idx_pa_student_class');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_payment_allocations');
    }
};
