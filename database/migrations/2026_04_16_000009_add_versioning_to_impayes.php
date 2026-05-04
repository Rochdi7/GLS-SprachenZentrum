<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impayes', function (Blueprint $table) {
            if (!Schema::hasColumn('impayes', 'dedup_key')) {
                $table->string('dedup_key', 255)->nullable()->after('reference')
                      ->comment('Cle de deduplication: site_id + student_name + fee_description + amount_due');
            }
            if (!Schema::hasColumn('impayes', 'auto_resolved')) {
                $table->boolean('auto_resolved')->default(false)->after('status')
                      ->comment('Marque automatiquement recouvré lors d un nouvel import');
            }
        });

        $indexExists = collect(DB::select(
            "SHOW INDEX FROM `impayes` WHERE Key_name = ?",
            ['impaye_dedup_idx']
        ))->isNotEmpty();

        if (! $indexExists) {
            Schema::table('impayes', function (Blueprint $table) {
                $table->index(['site_id', 'dedup_key'], 'impaye_dedup_idx');
            });
        }

        Schema::table('impaye_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('impaye_imports', 'new_rows')) {
                $table->unsignedInteger('new_rows')->default(0)->after('success_rows')
                      ->comment('Nouveaux impayes detectes');
            }
            if (!Schema::hasColumn('impaye_imports', 'resolved_rows')) {
                $table->unsignedInteger('resolved_rows')->default(0)->after('new_rows')
                      ->comment('Impayes resolus (ayant paye depuis le dernier import)');
            }
            if (!Schema::hasColumn('impaye_imports', 'kept_rows')) {
                $table->unsignedInteger('kept_rows')->default(0)->after('resolved_rows')
                      ->comment('Impayes encore actifs (presents dans les deux imports)');
            }
            if (!Schema::hasColumn('impaye_imports', 'previous_import_id')) {
                $table->foreignId('previous_import_id')->nullable()->after('kept_rows')
                      ->constrained('impaye_imports')->nullOnDelete()
                      ->comment('Import precedent pour le meme mois et centre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('impaye_imports', function (Blueprint $table) {
            $table->dropForeign(['previous_import_id']);
            $table->dropColumn(['new_rows', 'resolved_rows', 'kept_rows', 'previous_import_id']);
        });

        Schema::table('impayes', function (Blueprint $table) {
            $table->dropIndex('impaye_dedup_idx');
            $table->dropColumn(['dedup_key', 'auto_resolved']);
        });
    }
};
