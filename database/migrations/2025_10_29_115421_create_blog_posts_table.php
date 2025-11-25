<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // Correct category column
            $table->foreignId('category_id')
                  ->constrained('blog_categories')
                  ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            // Correct content field
            $table->longText('content');

            // Reading time (default 3 mins)
            $table->integer('reading_time')->default(3);

            // Featured (0/1)
            $table->boolean('featured')->default(false);

            // Status (draft/published)
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blog_posts');
    }
};
