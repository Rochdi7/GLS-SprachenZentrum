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
        Schema::create('homeschool_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('status')->default('pending'); // pending, success, failed
            $table->text('error_message')->nullable();
            $table->integer('records_synced')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homeschool_sync_logs');
    }
};
