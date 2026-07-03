<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds period-mode calculation + manual-override audit fields to
 * presence_import_students.
 *
 * SAFETY:
 *  - All new columns nullable.
 *  - Legacy weekly columns (week_N_*) are left completely untouched.
 *  - No enums, no renames, no drops, no data changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence_import_students', function (Blueprint $table) {
            // Total present days across the whole period (period mode input)
            $table->unsignedSmallInteger('period_presence_count')->nullable()->after('total_absent');
            // Auto amount the tier engine computed (frozen alongside override)
            $table->decimal('period_auto_amount', 10, 2)->nullable()->after('period_presence_count');
            // Manual override — wins over auto when not null
            $table->decimal('period_amount_override', 10, 2)->nullable()->after('period_auto_amount');

            // Audit trail for the manual override
            $table->string('override_reason', 500)->nullable()->after('period_amount_override');
            $table->foreignId('override_by')->nullable()->after('override_reason')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('override_at')->nullable()->after('override_by');
        });
    }

    public function down(): void
    {
        Schema::table('presence_import_students', function (Blueprint $table) {
            // Drop FK first, then columns (safe on MySQL)
            $table->dropConstrainedForeignId('override_by');
            $table->dropColumn([
                'period_presence_count',
                'period_auto_amount',
                'period_amount_override',
                'override_reason',
                'override_at',
            ]);
        });
    }
};
