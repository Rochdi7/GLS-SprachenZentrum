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

            // Identité étudiant (1 commande = 1 étudiant)
            $table->string('cin', 32)->index();
            $table->string('student_name');
            $table->string('phone', 32)->nullable();

            // Workflow
            $table->date('date_received')->nullable();
            $table->date('date_handed_over')->nullable();
            $table->enum('status', ['pending', 'translator', 'delivered'])->default('pending')->index();
            $table->text('notes')->nullable();

            // Total agrégé (somme des items)
            $table->unsignedInteger('total_cost')->default(0);

            $table->timestamps();
        });

        Schema::create('translation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id')
                ->constrained('translations')
                ->cascadeOnDelete();

            $table->string('doc_type');
            $table->unsignedInteger('page_count')->default(1);
            $table->unsignedInteger('price_per_page')->default(200);
            $table->unsignedInteger('line_total')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_items');
        Schema::dropIfExists('translations');
    }
};
