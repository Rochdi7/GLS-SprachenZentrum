<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence_import_students', function (Blueprint $table) {
            $table->unsignedTinyInteger('week_1_presence')->default(0)->after('total_absent');
            $table->unsignedTinyInteger('week_2_presence')->default(0)->after('week_1_presence');
            $table->unsignedTinyInteger('week_3_presence')->default(0)->after('week_2_presence');
            $table->unsignedTinyInteger('week_4_presence')->default(0)->after('week_3_presence');

            $table->decimal('week_1_amount', 10, 2)->default(0)->after('week_4_presence');
            $table->decimal('week_2_amount', 10, 2)->default(0)->after('week_1_amount');
            $table->decimal('week_3_amount', 10, 2)->default(0)->after('week_2_amount');
            $table->decimal('week_4_amount', 10, 2)->default(0)->after('week_3_amount');

            $table->decimal('week_1_amount_override', 10, 2)->nullable()->after('week_4_amount');
            $table->decimal('week_2_amount_override', 10, 2)->nullable()->after('week_1_amount_override');
            $table->decimal('week_3_amount_override', 10, 2)->nullable()->after('week_2_amount_override');
            $table->decimal('week_4_amount_override', 10, 2)->nullable()->after('week_3_amount_override');
        });

        Schema::table('presence_imports', function (Blueprint $table) {
            $table->unsignedTinyInteger('weekly_threshold')->default(3)->after('payment_per_student');
            $table->decimal('weekly_rate_percent', 5, 2)->default(25.00)->after('weekly_threshold');
        });

        Schema::table('presence_payment_summaries', function (Blueprint $table) {
            $table->unsignedInteger('count_qualified_weeks')->default(0)->after('count_zero');
            $table->unsignedInteger('count_unqualified_weeks')->default(0)->after('count_qualified_weeks');
            $table->decimal('weekly_unit_amount', 10, 2)->default(0)->after('count_unqualified_weeks');
        });
    }

    public function down(): void
    {
        Schema::table('presence_import_students', function (Blueprint $table) {
            $table->dropColumn([
                'week_1_presence', 'week_2_presence', 'week_3_presence', 'week_4_presence',
                'week_1_amount', 'week_2_amount', 'week_3_amount', 'week_4_amount',
                'week_1_amount_override', 'week_2_amount_override', 'week_3_amount_override', 'week_4_amount_override',
            ]);
        });

        Schema::table('presence_imports', function (Blueprint $table) {
            $table->dropColumn(['weekly_threshold', 'weekly_rate_percent']);
        });

        Schema::table('presence_payment_summaries', function (Blueprint $table) {
            $table->dropColumn(['count_qualified_weeks', 'count_unqualified_weeks', 'weekly_unit_amount']);
        });
    }
};
