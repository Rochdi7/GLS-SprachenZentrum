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
        // 1. CRM Students Mirror
        Schema::create('crm_students', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // 2. CRM Classes Mirror
        Schema::create('crm_classes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_id')->unique();
            $table->string('name');
            $table->bigInteger('crm_teacher_id')->nullable()->index();
            $table->string('level')->nullable();
            $table->integer('site_id')->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // 3. CRM Registrations (Pivot)
        Schema::create('crm_registrations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_id')->nullable()->unique();
            $table->bigInteger('crm_student_id')->index();
            $table->bigInteger('crm_class_id')->index();
            $table->string('status')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['crm_student_id', 'crm_class_id'], 'student_class_unique');
        });

        // 4. CRM Attendance Mirror
        Schema::create('crm_attendance', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_id')->nullable()->index();
            $table->bigInteger('crm_class_id')->index();
            $table->bigInteger('crm_student_id')->index();
            $table->date('date')->index();
            $table->boolean('is_present')->default(false);
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['crm_class_id', 'crm_student_id', 'date'], 'attendance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_attendance');
        Schema::dropIfExists('crm_registrations');
        Schema::dropIfExists('crm_classes');
        Schema::dropIfExists('crm_students');
    }
};
