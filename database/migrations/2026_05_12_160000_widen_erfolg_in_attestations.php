<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->string('erfolg', 100)->default('Erfolg')->change();
        });
    }

    public function down(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->string('erfolg', 30)->default('Erfolg')->change();
        });
    }
};
