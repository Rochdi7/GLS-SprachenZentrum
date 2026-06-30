<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks finished groups in crm_group_evolution_snapshot.
 *
 * is_finished — true when the group's formation has ended (class END_DATE is in
 * the past). Powers the "Groupes terminés" tab on the group-evolution dashboard,
 * which is separate from the active-groups view.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_group_evolution_snapshot', function (Blueprint $table) {
            $table->boolean('is_finished')->default(false)->after('actifs')->index();
        });
    }

    public function down(): void
    {
        Schema::table('crm_group_evolution_snapshot', function (Blueprint $table) {
            $table->dropColumn('is_finished');
        });
    }
};
