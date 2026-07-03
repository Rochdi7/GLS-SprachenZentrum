<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable audit trail for every payroll lifecycle action.
 *
 * One row per transition (and per notable action such as recalculation),
 * capturing who did what, the status before and after, when, and an optional
 * comment. Never updated or deleted in normal operation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presence_import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action', 40);            // e.g. validate, return_to_draft, mark_paid, lock, recalculate
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('comment')->nullable();
            $table->json('meta')->nullable();        // optional snapshot (e.g. payment info, amounts)

            $table->timestamps();

            $table->index(['presence_import_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_status_logs');
    }
};
