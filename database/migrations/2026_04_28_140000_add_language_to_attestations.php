<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            // de_fr (default — bilingue allemand+français comme le modèle)
            // de    (allemand uniquement)
            // fr    (français uniquement)
            // en    (anglais uniquement)
            $table->string('language', 6)->default('de_fr')->after('erfolg');
        });
    }

    public function down(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
