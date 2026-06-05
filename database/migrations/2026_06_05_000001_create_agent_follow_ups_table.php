<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_student_id')->index();
            $table->bigInteger('registration_id')->nullable()->index();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->enum('status', [
                'pending',
                'contacted',
                'no_answer',
                'interested',
                'not_interested',
                'solved',
            ])->default('pending');
            $table->text('note')->nullable();
            $table->date('follow_up_date')->nullable()->index();
            $table->timestamp('called_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['crm_student_id', 'status']);
            $table->index(['follow_up_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_follow_ups');
    }
};
