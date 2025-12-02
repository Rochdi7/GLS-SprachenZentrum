<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // Category relation
            $table->foreignId('category_id')
                ->constrained('blog_categories')
                ->cascadeOnDelete();

            // Multilangue fields (FR + EN)
            $table->string('title_fr');
            $table->string('title_en');

            $table->string('slug')->unique();

            $table->longText('content_fr');
            $table->longText('content_en');

            // Reading time
            $table->integer('reading_time')->default(3);

            // Featured
            $table->boolean('featured')->default(false);

            // VIEWS (needed for popular posts)
            $table->unsignedBigInteger('views')->default(0);

            // Status
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('blog_posts');
    }
};
