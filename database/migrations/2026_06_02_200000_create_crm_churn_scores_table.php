<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_churn_scores', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_student_id');
            $table->bigInteger('crm_store_id')->nullable();
            $table->tinyInteger('score')->unsigned()->default(0);
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->json('signals')->nullable();
            $table->string('student_name', 191)->nullable();
            $table->bigInteger('registration_id')->nullable();
            $table->bigInteger('class_id')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['crm_student_id', 'crm_store_id']);
            $table->index(['crm_store_id', 'risk_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_churn_scores');
    }
};
