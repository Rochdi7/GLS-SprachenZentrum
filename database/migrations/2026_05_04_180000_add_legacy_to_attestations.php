<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->boolean('is_legacy')->default(false)->after('group_id');
        });

        // Make group_id nullable so legacy attestations can be saved without a group.
        Schema::table('attestations', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attestations', function (Blueprint $table) {
            $table->dropColumn('is_legacy');
        });

        Schema::table('attestations', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable(false)->change();
        });
    }
};
