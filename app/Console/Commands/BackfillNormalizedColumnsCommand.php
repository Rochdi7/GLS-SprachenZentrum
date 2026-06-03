<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time backfill: populate normalized columns from existing raw_data JSON.
 *
 * Run once after deploying migration 2026_06_04_000001_normalize_crm_columns.
 * Safe to re-run — only updates rows where the normalized column is still NULL.
 *
 * After this command, the ongoing sync commands (crm:sync-attendance, etc.)
 * will populate the normalized columns on every future upsert automatically.
 *
 * Usage:
 *   php artisan crm:backfill-columns
 */
class BackfillNormalizedColumnsCommand extends Command
{
    protected $signature = 'crm:backfill-columns';
    protected $description = 'One-time backfill of normalized columns from raw_data JSON (run after migration)';

    public function handle(): int
    {
        $this->info('Backfilling normalized columns from raw_data...');
        $this->newLine();

        // ── crm_attendance.date_creation ────────────────────────────────────
        // raw_data->'$.DATE_CREATION' is an ISO datetime string like "2025-10-15T09:30:00"
        // NULL means draft (not formally saisied), non-NULL means recorded session
        $this->info('[1/5] crm_attendance.date_creation');
        $rows = DB::statement("
            UPDATE crm_attendance
            SET date_creation = JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.DATE_CREATION'))
            WHERE date_creation IS NULL
              AND JSON_EXTRACT(raw_data, '$.DATE_CREATION') IS NOT NULL
              AND JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.DATE_CREATION')) != 'null'
              AND JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.DATE_CREATION')) != ''
        ");
        $affected = DB::connection()->getPdo()->lastInsertId();
        $this->line("   Done");

        // ── crm_attendance.session_reference ────────────────────────────────
        $this->info('[2/5] crm_attendance.session_reference');
        DB::statement("
            UPDATE crm_attendance
            SET session_reference = JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.SESSION_REFERENCE'))
            WHERE session_reference IS NULL
              AND JSON_EXTRACT(raw_data, '$.SESSION_REFERENCE') IS NOT NULL
              AND JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.SESSION_REFERENCE')) != 'null'
        ");
        $this->line("   Done");

        // ── crm_registrations.date_creation ─────────────────────────────────
        // DATE_CREATION in registrations is stored in local time (no UTC offset)
        // so we use DATE() directly without timezone conversion
        $this->info('[3/5] crm_registrations.date_creation');
        DB::statement("
            UPDATE crm_registrations
            SET date_creation = DATE(JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.DATE_CREATION')))
            WHERE date_creation IS NULL
              AND JSON_EXTRACT(raw_data, '$.DATE_CREATION') IS NOT NULL
              AND JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.DATE_CREATION')) != 'null'
              AND JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.DATE_CREATION')) != ''
        ");
        $this->line("   Done");

        // ── crm_registrations.status_label ──────────────────────────────────
        $this->info('[4/5] crm_registrations.status_label');
        DB::statement("
            UPDATE crm_registrations
            SET status_label = JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.REGISTRATION_STATUS_NAME'))
            WHERE status_label IS NULL
              AND JSON_EXTRACT(raw_data, '$.REGISTRATION_STATUS_NAME') IS NOT NULL
              AND JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.REGISTRATION_STATUS_NAME')) != 'null'
        ");
        $this->line("   Done");

        // ── crm_payment_snapshots.date_creation_date ────────────────────────
        // date_creation column already exists as a timestamp — we just extract DATE()
        $this->info('[5/5] crm_payment_snapshots.date_creation_date');
        DB::statement("
            UPDATE crm_payment_snapshots
            SET date_creation_date = DATE(date_creation)
            WHERE date_creation_date IS NULL
              AND date_creation IS NOT NULL
        ");
        $this->line("   Done");

        $this->newLine();
        $this->info('[DONE] All columns backfilled. You can now run: php artisan crm:sync-all');

        return self::SUCCESS;
    }
}
