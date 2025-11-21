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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('last_name');      
            $table->string('first_name');     
            $table->date('birth_date');        
            $table->string('birth_place')->nullable(); 

            // Exam Meta
            $table->string('exam_level')->default('Deutsch B2');
            $table->date('exam_date');         
            $table->date('issue_date');       
            $table->string('certificate_number')->unique();

            // Written Exam Scores
            $table->integer('written_total');  
            $table->integer('written_max')->default(225);

            $table->integer('reading_score');  
            $table->integer('reading_max')->default(75);

            $table->integer('grammar_score');  
            $table->integer('grammar_max')->default(30);

            $table->integer('listening_score');
            $table->integer('listening_max')->default(75);

            $table->integer('writing_score'); 
            $table->integer('writing_max')->default(45);

            // Oral Exam Scores
            $table->integer('oral_total');    
            $table->integer('oral_max')->default(75);

            $table->integer('presentation_score');
            $table->integer('presentation_max')->default(25);

            $table->integer('discussion_score');
            $table->integer('discussion_max')->default(25);

            $table->integer('problemsolving_score');
            $table->integer('problemsolving_max')->default(25);

            // Final Result
            $table->string('final_result');  

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
