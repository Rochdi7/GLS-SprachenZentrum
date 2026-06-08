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
        Schema::create('crm_resync_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 50);                // 'attendance', 'payments', 'all', …
            $table->string('domain_label', 100);         // human-readable label
            $table->string('status', 20);                // ok | partial | error | locked
            $table->integer('crm_store_id')->nullable(); // center filter; null = all centers
            $table->json('steps')->nullable();           // [{step, status, elapsed, error?}, …]
            $table->text('error_message')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['domain',  'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_resync_log');
    }
};
