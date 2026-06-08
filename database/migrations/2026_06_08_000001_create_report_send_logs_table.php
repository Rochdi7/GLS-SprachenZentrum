<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_send_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60);           // weekly-presence, weekly-prof-payment …
            $table->string('category', 20);        // weekly | monthly
            $table->date('period_from');
            $table->date('period_to');
            $table->json('recipients');
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_send_logs');
    }
};
