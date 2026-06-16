<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_group_profitability', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('crm_class_id')->index();
            $table->string('class_name', 255)->nullable();
            $table->unsignedBigInteger('crm_store_id')->nullable()->index();
            $table->string('site_name', 191)->nullable();
            $table->string('teacher_name', 191)->nullable();
            $table->string('level_name', 191)->nullable();

            // Period — 'YYYY-MM'
            $table->char('period_month', 7)->index();
            $table->enum('period_type', ['monthly', 'annual'])->default('monthly');

            // Revenue from crm_payment_allocations grouped by class_id
            $table->decimal('revenue', 14, 2)->default(0);
            $table->unsignedSmallInteger('paying_students')->default(0);

            // Teacher salary from site_expenses WHERE type = 'paiement_prof'
            $table->decimal('teacher_salary', 14, 2)->default(0);
            // 'direct' = label matched class name, 'proportional' = revenue-share fallback, 'equal' = no revenue data
            $table->string('salary_match_method', 32)->nullable();

            // Other expenses attributed to this class
            $table->decimal('other_expenses', 14, 2)->default(0);

            // Computed
            $table->decimal('profit', 14, 2)->default(0);
            $table->decimal('margin_pct', 6, 2)->default(0);

            // Attendance from crm_presence_summary
            $table->decimal('attendance_rate', 5, 2)->nullable();
            $table->unsignedSmallInteger('total_sessions')->default(0);
            $table->unsignedSmallInteger('total_present')->default(0);
            $table->unsignedSmallInteger('total_absent')->default(0);

            // Student count from crm_registrations
            $table->unsignedSmallInteger('active_students')->default(0);

            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['crm_class_id', 'period_month', 'period_type'], 'uniq_prof_class_period');
            $table->index(['crm_store_id', 'period_month'], 'idx_prof_store_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_group_profitability');
    }
};
