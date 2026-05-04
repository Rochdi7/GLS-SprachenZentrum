<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('site_expenses', 'reference')) {
                $table->string('reference', 50)->nullable()->after('id')->comment('Ref DP55, DP61, etc.');
            }
            if (! Schema::hasColumn('site_expenses', 'expense_date')) {
                $table->date('expense_date')->nullable()->after('month')->comment('Date effective de la depense');
            }
            if (! Schema::hasColumn('site_expenses', 'payment_method')) {
                $table->string('payment_method', 30)->nullable()->after('expense_date')->comment('Methode de paiement');
            }
            if (! Schema::hasColumn('site_expenses', 'operator_name')) {
                $table->string('operator_name')->nullable()->after('payment_method')->comment('Operateur');
            }
            if (! Schema::hasColumn('site_expenses', 'order_number')) {
                $table->unsignedInteger('order_number')->nullable()->after('operator_name');
            }
            if (! Schema::hasColumn('site_expenses', 'expense_import_id')) {
                $table->foreignId('expense_import_id')->nullable()->after('notes')
                      ->comment('Lien vers import source');
            }
        });

        \DB::statement("ALTER TABLE site_expenses MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'autre'");
    }

    public function down(): void
    {
        Schema::table('site_expenses', function (Blueprint $table) {
            $columns = array_filter(
                ['reference', 'expense_date', 'payment_method', 'operator_name', 'order_number', 'expense_import_id'],
                fn ($c) => Schema::hasColumn('site_expenses', $c)
            );
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
