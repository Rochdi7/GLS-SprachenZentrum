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
        Schema::table('crm_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id')->nullable()->index()->after('crm_id');
        });
    }

    public function down(): void
    {
        Schema::table('crm_classes', function (Blueprint $table) {
            $table->dropColumn('class_id');
        });
    }
};
