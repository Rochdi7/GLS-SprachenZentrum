<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment metadata + lifecycle "who/when" audit columns on presence_imports.
 *
 * SAFETY: all nullable, no existing columns touched, fully backward compatible.
 * The detailed transition trail lives in payroll_status_logs; these columns are
 * denormalised convenience fields for fast display (who validated/paid/locked).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            // Payment info (required by the app when moving to "paid", not by DB)
            $table->date('payment_date')->nullable()->after('final_total');
            $table->string('payment_method', 30)->nullable()->after('payment_date');
            $table->string('payment_reference', 100)->nullable()->after('payment_method');
            $table->text('payment_notes')->nullable()->after('payment_reference');

            // Lifecycle "who + when" (denormalised for display)
            $table->foreignId('validated_by')->nullable()->after('payment_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->after('validated_by');
            $table->foreignId('paid_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable()->after('paid_by');
            $table->foreignId('locked_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable()->after('locked_by');
        });
    }

    public function down(): void
    {
        Schema::table('presence_imports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by');
            $table->dropConstrainedForeignId('paid_by');
            $table->dropConstrainedForeignId('locked_by');
            $table->dropColumn([
                'payment_date',
                'payment_method',
                'payment_reference',
                'payment_notes',
                'validated_at',
                'paid_at',
                'locked_at',
            ]);
        });
    }
};
