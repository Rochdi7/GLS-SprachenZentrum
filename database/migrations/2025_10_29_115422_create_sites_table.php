<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();

            $table->string('name'); 
            $table->string('slug')->unique(); 
            $table->string('city'); 
            $table->string('address')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Hero
            $table->string('subtitle')->nullable();
            $table->string('hero_image')->nullable(); // medialibrary compatible

            // About sections
            $table->longText('about_title')->nullable();
            $table->longText('about_subtitle')->nullable();
            $table->longText('about_content')->nullable();

            // "What We Offer" section
            $table->longText('offer_title')->nullable();
            $table->longText('offer_subtitle')->nullable();
            $table->longText('offer_content')->nullable();

            // 9onsol video block
            $table->string('video_title')->nullable();
            $table->longText('video_description')->nullable();
            $table->string('video_url')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sites');
    }
};
