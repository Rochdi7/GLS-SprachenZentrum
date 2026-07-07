<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            // Niveau de départ (optionnel) — quand renseigné, l'attestation couvre
            // un parcours de level_from à level (ex : A1 → B1 coche A1, A2 et B1).
            $table->string('level_from', 5)->nullable()->after('level');
        });
    }

    public function down(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->dropColumn('level_from');
        });
    }
};
