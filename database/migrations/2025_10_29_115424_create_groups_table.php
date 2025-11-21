<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();

            // Example: A1 – A2 – B1 – B2
            $table->enum('level', ['A1', 'A2', 'B1', 'B2']); 

            // Example: "Morning Groups"
            $table->string('period_label'); 

            // Example: "10:00 – 12:30"
            $table->string('time_range');   

            $table->longText('description')->nullable();

            $table->timestamps();

            $table->index('site_id');
            $table->index('teacher_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('groups');
    }
};
