<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Allow a user to be affected to multiple centres (sites).
 *
 * - Adds the pivot `site_user` (user_id, site_id).
 * - Backfills it from the existing `users.site_id` so nothing changes for
 *   users that already have a primary centre.
 * - `users.site_id` is kept as the "primary / default" centre so legacy
 *   filters (planning, encaissements, primes, etc.) keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_user')) {
            Schema::create('site_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('site_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'site_id']);
            });
        }

        // Backfill: mirror current single site_id into the pivot
        if (Schema::hasColumn('users', 'site_id')) {
            $rows = DB::table('users')->whereNotNull('site_id')->get(['id', 'site_id']);
            $now  = now();
            foreach ($rows as $r) {
                DB::table('site_user')->updateOrInsert(
                    ['user_id' => $r->id, 'site_id' => $r->site_id],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_user');
    }
};
