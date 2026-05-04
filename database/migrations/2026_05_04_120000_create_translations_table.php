<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();

            $table->string('cin', 32)->index();
            $table->string('student_name');
            $table->string('phone', 32)->nullable();
            $table->string('doc_type')->nullable();

            $table->unsignedInteger('page_count')->default(1);
            $table->unsignedInteger('price_per_page')->default(200);
            $table->unsignedInteger('total_cost')->default(0);

            $table->date('date_received')->nullable();
            $table->date('date_handed_over')->nullable();

            $table->enum('status', ['pending', 'translator', 'delivered'])->default('pending')->index();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
