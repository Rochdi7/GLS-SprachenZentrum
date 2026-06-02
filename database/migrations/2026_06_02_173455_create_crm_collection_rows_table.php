<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crm_collection_rows', function (Blueprint $table) {
            $table->id();
            $table->string('crm_id', 100)->unique();   // e.g. "217993_SUBSCRIPTION_SERVICE"
            $table->unsignedInteger('crm_store_id')->nullable()->index();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('student_name')->nullable();
            $table->string('store_name')->nullable();
            $table->decimal('total_price', 14, 2)->nullable();
            $table->decimal('rest_amount', 14, 2)->nullable()->index();
            $table->date('due_date')->nullable()->index();
            $table->integer('payment_delay_days')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable()->index();
            $table->unsignedInteger('registration_status_id')->nullable();
            $table->string('registration_status_name')->nullable();
            $table->string('service_type_name')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_collection_rows');
    }
};
