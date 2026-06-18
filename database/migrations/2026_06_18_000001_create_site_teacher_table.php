<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Allow a teacher to be affected to multiple centres (sites).
 *
 * - Adds the pivot `site_teacher` (teacher_id, site_id).
 * - Backfills it from the existing `teachers.site_id` so nothing changes for
 *   teachers that already have a primary centre.
 * - `teachers.site_id` is kept as the "primary / default" centre so legacy
 *   relations (groups, attestations, CRM mirror sync) keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_teacher')) {
            Schema::create('site_teacher', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('site_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['teacher_id', 'site_id']);
            });
        }

        // Backfill: mirror current single site_id into the pivot
        if (Schema::hasColumn('teachers', 'site_id')) {
            $rows = DB::table('teachers')->whereNotNull('site_id')->get(['id', 'site_id']);
            $now  = now();
            foreach ($rows as $r) {
                DB::table('site_teacher')->updateOrInsert(
                    ['teacher_id' => $r->id, 'site_id' => $r->site_id],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_teacher');
    }
};
