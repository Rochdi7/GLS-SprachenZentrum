<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            $table->boolean('is_crm_api')->default(false)->after('imported_by');
        });

        Schema::table('presence_import_students', function (Blueprint $table) {
            $table->unsignedBigInteger('crm_student_id')->nullable()->after('raw_data');
        });
    }

    public function down(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            $table->dropColumn('is_crm_api');
        });

        Schema::table('presence_import_students', function (Blueprint $table) {
            $table->dropColumn('crm_student_id');
        });
    }
};
