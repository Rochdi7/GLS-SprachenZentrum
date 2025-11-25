 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            /**
             * Personal Information
             */
            $table->string('last_name');
            $table->string('first_name');
            $table->date('birth_date');
            $table->string('birth_place')->nullable();

            /**
             * Exam Meta
             */
            $table->string('exam_level')->default('Deutsch B2');
            $table->date('exam_date');
            $table->date('issue_date');
            $table->string('certificate_number')->unique();

            /**
             * Schriftliche Prüfung (Written Exam)
             */
            $table->integer('reading_score');       // Leseverstehen
            $table->integer('grammar_score');       // Sprachbausteine
            $table->integer('listening_score');     // Hörverstehen
            $table->integer('writing_score');       // Schriftlicher Ausdruck

            $table->integer('written_total');       // Calculé par toi (ou stocké)

            /**
             * Mündliche Prüfung (Oral Exam)
             */
            $table->integer('presentation_score');  // Präsentation
            $table->integer('discussion_score');    // Diskussion
            $table->integer('problemsolving_score');// Problemlösung

            $table->integer('oral_total');          // Calculé

            /**
             * Final Result
             */
            $table->string('final_result'); // Befriedigend / Gut / Sehr gut etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
