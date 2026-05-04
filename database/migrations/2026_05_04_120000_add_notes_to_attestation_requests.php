<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestation_requests', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('level');
        });
    }

    public function down(): void
    {
        Schema::table('attestation_requests', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
