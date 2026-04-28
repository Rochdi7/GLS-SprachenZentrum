<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->date('course_start_date')->nullable()->change();
            $table->date('course_end_date')->nullable()->change();
            $table->date('niveau_start_date')->nullable()->change();
            $table->date('niveau_end_date')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->string('birth_place')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->date('course_start_date')->nullable(false)->change();
            $table->date('course_end_date')->nullable(false)->change();
            $table->date('niveau_start_date')->nullable(false)->change();
            $table->date('niveau_end_date')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->string('birth_place')->nullable(false)->change();
        });
    }
};
