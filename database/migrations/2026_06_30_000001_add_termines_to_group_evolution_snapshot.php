<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the "terminés" bucket to crm_group_evolution_snapshot.
 *
 * terminés — students who finished the formation: they have a non-inscription
 * payment in the class END_DATE month (i.e. they paid the group's last month).
 * This is independent of the "quittants" (Annulé) departure count.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_group_evolution_snapshot', function (Blueprint $table) {
            $table->unsignedSmallInteger('termines')->default(0)->after('quittants');
        });
    }

    public function down(): void
    {
        Schema::table('crm_group_evolution_snapshot', function (Blueprint $table) {
            $table->dropColumn('termines');
        });
    }
};
