<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->text('methodology_text')->nullable()->after('erfolg');
        });
    }

    public function down(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->dropColumn('methodology_text');
        });
    }
};
