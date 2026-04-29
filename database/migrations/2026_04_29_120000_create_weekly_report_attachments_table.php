<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_report_id')
                ->constrained('weekly_reports')
                ->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
            $table->index(['weekly_report_id', 'created_at']);
        });

        // Backfill: each existing weekly_reports row that has a single attachment
        // becomes one row in weekly_report_attachments.
        if (Schema::hasColumn('weekly_reports', 'attachment_path')) {
            DB::table('weekly_reports')
                ->whereNotNull('attachment_path')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    $now = now();
                    $insert = [];
                    foreach ($rows as $r) {
                        $insert[] = [
                            'weekly_report_id' => $r->id,
                            'path'             => $r->attachment_path,
                            'original_name'    => $r->attachment_original_name ?? null,
                            'created_at'       => $r->updated_at ?? $r->created_at ?? $now,
                            'updated_at'       => $r->updated_at ?? $r->created_at ?? $now,
                        ];
                    }
                    if (!empty($insert)) {
                        DB::table('weekly_report_attachments')->insert($insert);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_report_attachments');
    }
};
