<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('studienkollegs', function (Blueprint $table) {

    // Application
    $table->string('application_method')->nullable();
    $table->string('application_portal_note')->nullable();

    // Requirements
    $table->json('requirements')->nullable();

    // Certification
    $table->boolean('certification_required')->default(false);
    $table->boolean('translation_required')->default(false);
    $table->string('translation_note')->nullable();

    // Exam
    $table->string('exam_subjects')->nullable();
    $table->string('exam_link')->nullable();

    // Map
    $table->text('map_embed')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('studienkollegs', function (Blueprint $table) {
            //
        });
    }
};
