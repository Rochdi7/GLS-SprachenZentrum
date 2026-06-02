<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            $table->string('month_label')->nullable()->after('month');
            $table->string('crm_teacher_name')->nullable()->after('month_label');
        });
    }

    public function down(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            $table->dropColumn(['month_label', 'crm_teacher_name']);
        });
    }
};
