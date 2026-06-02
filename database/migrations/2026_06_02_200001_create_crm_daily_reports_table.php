<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();
            $table->json('payload')->nullable();
            $table->decimal('revenue_yesterday', 14, 2)->nullable();
            $table->integer('new_registrations')->nullable();
            $table->decimal('outstanding_receivables', 14, 2)->nullable();
            $table->integer('students_at_risk')->nullable();
            $table->string('best_center', 100)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_daily_reports');
    }
};
