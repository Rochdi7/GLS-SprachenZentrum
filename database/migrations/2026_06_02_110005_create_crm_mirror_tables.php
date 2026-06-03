<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

            // Merged from add_class_id_to_crm_classes_table
            $table->unsignedBigInteger('class_id')->nullable()->index('class_id_idx');
        });

        // 3. CRM Registrations (Pivot)
        Schema::create('crm_registrations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_id')->nullable()->unique();
            $table->bigInteger('crm_student_id')->index();
            $table->bigInteger('crm_class_id')->index();
            $table->string('status')->nullable();

            // Normalized columns (merged from add_store_id + normalize_crm_columns)
            $table->integer('crm_store_id')->nullable()->index('crm_store_id_idx');
            $table->date('date_creation')->nullable()->index('idx_reg_date_creation');
            $table->string('status_label', 64)->nullable();

            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['crm_student_id', 'crm_class_id'], 'student_class_unique');
            $table->index(['crm_store_id', 'date_creation'], 'idx_reg_store_date');
            $table->index(['crm_store_id', 'status_label'], 'idx_reg_store_status');
        });

        // 4. CRM Attendance Mirror
        Schema::create('crm_attendance', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_id')->nullable()->index();
            $table->bigInteger('crm_class_id')->index();
            $table->bigInteger('crm_student_id')->index();
            $table->date('date')->index();
            $table->boolean('is_present')->default(false);

            // Normalized columns (merged from normalize_crm_columns)
            $table->datetime('date_creation')->nullable()->index('idx_att_date_creation');
            $table->string('session_reference', 64)->nullable();

            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['crm_class_id', 'crm_student_id', 'date'], 'attendance_unique');
            $table->index(['crm_class_id', 'date'], 'idx_att_class_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_attendance');
        Schema::dropIfExists('crm_registrations');
        Schema::dropIfExists('crm_classes');
        Schema::dropIfExists('crm_students');
    }
};
