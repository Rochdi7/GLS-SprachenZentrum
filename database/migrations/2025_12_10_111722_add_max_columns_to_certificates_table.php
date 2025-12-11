<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {

            // Written Max values
            $table->integer('reading_max')->default(75);
            $table->integer('grammar_max')->default(30);
            $table->integer('listening_max')->default(75);
            $table->integer('writing_max')->default(45);

            // Oral Max values
            $table->integer('presentation_max')->default(25);
            $table->integer('discussion_max')->default(25);
            $table->integer('problemsolving_max')->default(25);

            // Totals Max
            $table->integer('written_max')->default(225);
            $table->integer('oral_max')->default(75);
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn([
                'reading_max',
                'grammar_max',
                'listening_max',
                'writing_max',
                'presentation_max',
                'discussion_max',
                'problemsolving_max',
                'written_max',
                'oral_max',
            ]);
        });
    }
};
