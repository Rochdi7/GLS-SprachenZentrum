<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('encaissement_imports', 'month')) {
            return;
        }

        Schema::table('encaissement_imports', function (Blueprint $table) {
            $table->string('month', 7)->nullable()->after('school_year')
                  ->comment('Mois de l import au format YYYY-MM');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('encaissement_imports', 'month')) {
            return;
        }

        Schema::table('encaissement_imports', function (Blueprint $table) {
            $table->dropColumn('month');
        });
    }
};
