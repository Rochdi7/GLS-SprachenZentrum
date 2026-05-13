<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('site_name_snapshot')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('site_id');
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
