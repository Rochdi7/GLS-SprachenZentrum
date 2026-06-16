<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_alerts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('crm_store_id')->nullable()->index();

            // 'absent_student' | 'unpaid_30d' | 'cheque_due_soon' | 'weak_attendance' | 'group_near_end'
            $table->string('alert_type', 64)->index();

            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');

            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->json('payload')->nullable();

            // Entity references
            $table->unsignedBigInteger('crm_student_id')->nullable()->index();
            $table->unsignedBigInteger('crm_class_id')->nullable()->index();

            // Lifecycle
            $table->enum('status', ['open', 'in_progress', 'resolved', 'dismissed'])->default('open')->index();
            $table->string('resolved_by', 191)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();

            // Deduplication — same alert_type + same entity + same month = upsert not duplicate
            $table->string('dedup_key', 191)->unique();

            $table->timestamp('detected_at')->nullable();
            $table->timestamps();

            $table->index(['crm_store_id', 'status', 'severity'], 'idx_alerts_store_status_sev');
            $table->index(['alert_type', 'status'], 'idx_alerts_type_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_alerts');
    }
};
