<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily snapshot of every payment row returned by the Wimschool CRM API.
 *
 * One row per (crm_payment_id, snapshot_date). Each day's nightly job upserts
 * the current state; the diff page compares yesterday vs today to surface:
 *   - Deleted payments (yesterday has a row, today doesn't)
 *   - Amount changed
 *   - User_update changed (someone touched it)
 *
 * The full API row JSON is stored in `payload` for forensic detail without
 * having to keep every column individually.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crm_payment_snapshots')) {
            return;
        }

        Schema::create('crm_payment_snapshots', function (Blueprint $table) {
            $table->id();

            // Identity from the API
            $table->unsignedBigInteger('crm_payment_id');
            $table->date('snapshot_date');

            // Core fields we want to query/filter without parsing the JSON
            $table->unsignedBigInteger('crm_store_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('reference', 64)->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->string('payment_method_name', 100)->nullable();
            $table->unsignedInteger('payment_type_id')->nullable();
            $table->string('payment_type_name', 100)->nullable();
            $table->unsignedBigInteger('user_creation_id')->nullable();
            $table->string('user_creation_full_name', 191)->nullable();
            $table->unsignedBigInteger('user_update_id')->nullable();
            $table->string('user_update_full_name', 191)->nullable();
            $table->timestamp('date_creation')->nullable();
            $table->timestamp('date_update')->nullable();

            // Full payload + a hash so the next day's job can detect changes cheaply
            $table->json('payload');
            $table->string('payload_hash', 64);

            $table->timestamps();

            $table->unique(['crm_payment_id', 'snapshot_date']);
            $table->index(['snapshot_date', 'crm_store_id']);
            $table->index(['user_creation_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_payment_snapshots');
    }
};
