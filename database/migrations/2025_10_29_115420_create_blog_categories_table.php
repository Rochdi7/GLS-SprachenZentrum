<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // Example: "German Courses"
            $table->string('slug')->unique();  // german-courses
            $table->boolean('is_active')->default(true);
            $table->integer('position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blog_categories');
    }
};
