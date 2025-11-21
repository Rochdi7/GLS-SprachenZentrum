<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('speciality')->nullable(); 
            $table->longText('bio')->nullable(); 

            $table->timestamps();

            $table->index('site_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('teachers');
    }
};
