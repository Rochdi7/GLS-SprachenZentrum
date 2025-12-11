<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();

            // Default name
            $table->string('name'); // fallback

            // Multi-lang names
            $table->string('name_fr')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable(); // future
            $table->string('name_de')->nullable(); // future

            // Level (FIXED: A1 → B2 ONLY)
            $table->enum('level', ['A1', 'A2', 'B1', 'B2']);

            // Periods
            $table->enum('period_label', ['morning', 'midday', 'afternoon', 'evening']);

            // Hours
            $table->string('time_range');

            // Status
            $table->enum('status', ['active', 'upcoming'])->default('active');

            $table->timestamps();

            // Indexes
            $table->index('site_id');
            $table->index('teacher_id');

            /********************************************
             * SUIVI DU GROUPE (new section added)
             ********************************************/
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
