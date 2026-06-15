<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->date('week_start');           // Monday of the week (ISO)
            $table->date('week_end');             // Sunday of the week
            $table->string('week_label');         // e.g. "S24 — 09/06 au 15/06/2026"
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->integer('new_registrations')->default(0);
            $table->integer('active_students')->nullable();
            $table->decimal('outstanding_receivables', 12, 2)->nullable();
            $table->string('best_center')->nullable();
            $table->json('centers_ranking')->nullable(); // [{name, amount}, ...]
            $table->json('daily_breakdown')->nullable(); // [{date, revenue, registrations}, ...]
            $table->json('payload')->nullable();         // full raw data
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique('week_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_weekly_reports');
    }
};
