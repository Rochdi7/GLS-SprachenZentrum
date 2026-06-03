<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks the progress of each step inside crm:sync-all.
 *
 * WHY THIS TABLE EXISTS:
 * Shared hosting cron jobs can be killed mid-execution (memory limit, timeout).
 * Without a progress log, a failed sync would restart from zero next run,
 * potentially re-running expensive API calls that already succeeded.
 *
 * crm:sync-all writes a row per step with status running → done | failed.
 * The --resume flag reads this table and skips steps already marked done today.
 * The --from=step flag jumps to a specific step regardless of this table.
 *
 * Used by: CrmSyncAllCommand
 *
 * Manual inspection:
 *   SELECT step, status, completed_at, last_error FROM crm_sync_log ORDER BY id;
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_sync_log', function (Blueprint $table) {
            $table->id();

            // Step name matches the key in CrmSyncAllCommand::STEPS
            // e.g. 'classes', 'attendance', 'allocations', 'group_evolution'
            $table->string('step', 64)->unique();

            $table->enum('status', ['pending', 'running', 'done', 'failed'])
                  ->default('pending');

            $table->unsignedInteger('records_synced')->default(0);

            // Incremented each time this step is attempted (useful for debugging flaky steps)
            $table->unsignedTinyInteger('attempts')->default(0);

            // Last error message — null when status = done
            $table->text('last_error')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_sync_log');
    }
};
