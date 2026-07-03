<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hikvision_devices', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('name');
            $table->string('serial_number')->nullable()->index();
            $table->string('ip_address')->nullable()->index();
            $table->string('status', 32)->nullable()->index();
            $table->string('firmware_version')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('hikvision_persons', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('employee_no')->nullable()->index();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('status', 32)->nullable()->index();
            $table->string('department')->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('hikvision_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique();
            $table->foreignId('hikvision_device_id')->nullable()->constrained('hikvision_devices')->nullOnDelete();
            $table->foreignId('hikvision_person_id')->nullable()->constrained('hikvision_persons')->nullOnDelete();
            $table->string('device_external_id')->nullable()->index();
            $table->string('person_external_id')->nullable()->index();
            $table->string('direction', 16)->nullable()->index();
            $table->string('verification_mode', 64)->nullable();
            $table->string('status', 32)->nullable()->index();
            $table->timestamp('occurred_at')->index();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamps();

            $table->index(['person_external_id', 'occurred_at'], 'hikvision_attendance_person_occured_idx');
            $table->index(['device_external_id', 'occurred_at'], 'hikvision_attendance_device_occured_idx');
        });

        Schema::create('hikvision_alarms', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique();
            $table->foreignId('hikvision_device_id')->nullable()->constrained('hikvision_devices')->nullOnDelete();
            $table->string('device_external_id')->nullable()->index();
            $table->string('alarm_type')->index();
            $table->string('severity', 32)->nullable()->index();
            $table->string('status', 32)->nullable()->index();
            $table->timestamp('triggered_at')->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('hikvision_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_uuid')->nullable()->unique();
            $table->string('event_type')->index();
            $table->string('source')->nullable()->index();
            $table->string('signature_masked')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->useCurrent()->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->string('status', 32)->default('received')->index();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('hikvision_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index();
            $table->string('action')->index();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedInteger('records_total')->default(0);
            $table->unsignedInteger('records_success')->default(0);
            $table->unsignedInteger('records_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hikvision_sync_logs');
        Schema::dropIfExists('hikvision_webhook_events');
        Schema::dropIfExists('hikvision_alarms');
        Schema::dropIfExists('hikvision_attendance_records');
        Schema::dropIfExists('hikvision_persons');
        Schema::dropIfExists('hikvision_devices');
    }
};
