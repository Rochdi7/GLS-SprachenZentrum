<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'staff_role')) {
            DB::table('users')
                ->whereIn('staff_role', ['Réception', 'RÃ©ception'])
                ->update(['staff_role' => 'Conseiller Commercial']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'staff_role')) {
            DB::table('users')
                ->where('staff_role', 'Conseiller Commercial')
                ->update(['staff_role' => 'Réception']);
        }
    }
};
