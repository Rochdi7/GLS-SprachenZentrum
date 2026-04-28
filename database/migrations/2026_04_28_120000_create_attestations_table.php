<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attestations', function (Blueprint $table) {
            $table->id();

            // Étudiant
            $table->string('last_name');
            $table->string('first_name');
            $table->date('birth_date');
            $table->string('birth_place');

            // Lien groupe + niveau choisi
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('level', 5); // A1 / A2 / B1 / B2 / C1

            // Période globale du cours (vom ... bis)
            $table->date('course_start_date');
            $table->date('course_end_date');

            // Période du niveau sélectionné (Das Niveau beginnt von ... bis ...)
            $table->date('niveau_start_date');
            $table->date('niveau_end_date');

            // Calcul auto Unterrichtseinheiten 45 min
            $table->integer('units_45min');
            $table->decimal('hours_per_session', 4, 2); // 2.00 ou 2.50

            // Frais : full / partial
            $table->string('fees_status', 20)->default('full'); // 'full' | 'partial'

            // Kursinfo : Stufe X von Y
            $table->unsignedTinyInteger('stufe_index')->default(1);  // X
            $table->unsignedTinyInteger('stufe_total')->default(3);  // Y

            // Erfolg : Erfolg / mit gutem Erfolg / mit Erfolg / teilgenommen
            $table->string('erfolg', 30)->default('Erfolg');

            // Ort + Datum bas du document
            $table->string('city');
            $table->date('issue_date');

            // Numéro / token
            $table->string('attestation_number')->unique();
            $table->string('public_token', 64)->unique()->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attestations');
    }
};
