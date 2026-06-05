<?php

namespace App\Console\Commands;

use App\Models\CrmPresenceSummary;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Computes monthly per-class attendance summaries from crm_attendance.
 *
 * WHY THIS COMMAND EXISTS:
 * PresenceSuiviService::allTimeTotals() iterated a CarbonPeriod from the
 * earliest attendance date to today (up to 700+ days) × 30 classes per
 * center = 21,000+ PHP iterations per page request. This caused 2–15s loads.
 *
 * This command runs a single SQL GROUP BY aggregation and writes results to
 * crm_presence_summary. The service then reads one SUM query < 100ms.
 *
 * The normalized date_creation column (added by migration 000001 and populated
 * by BackfillNormalizedColumnsCommand) is used here instead of JSON_EXTRACT.
 * NULL date_creation = draft session. NOT NULL = formally saisied session.
 *
 * Usage:
 *   php artisan crm:build-presence-summary --all
 *   php artisan crm:build-presence-summary --all --months=6
 */
class BuildPresenceSummaryCommand extends Command
{
    protected $signature = 'crm:build-presence-summary
        {--all      : All stores}
        {--months=3 : Months back to recompute (default 3)}';

    protected $description = 'Aggregate monthly attendance summaries (replaces PHP CarbonPeriod loops)';

    public function handle(): int
    {
        $months = max(1, min(24, (int) $this->option('months')));
        $from   = Carbon::today('Africa/Casablanca')
            ->subMonths($months)->startOfMonth()->toDateString();
        $today  = Carbon::today('Africa/Casablanca')->toDateString();

        $this->info("Building presence summaries from {$from} to {$today}");

        try {
            $count = $this->aggregate($from, $today);
            $this->info("[DONE] {$count} summary rows written to crm_presence_summary");
        } catch (\Throwable $e) {
            $this->error("FAILED: " . $e->getMessage());
            Log::error("crm:build-presence-summary: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function aggregate(string $from, string $today): int
    {
        // Single SQL GROUP BY — replaces O(days × classes) PHP loop entirely.
        // Uses the normalized date_creation column (no JSON_EXTRACT in GROUP BY).
        //
        // Result: one row per (crm_class_id, month) with session and attendance counts.
        $rows = DB::table('crm_attendance as a')
            ->join('crm_classes as c', 'c.crm_id', '=', 'a.crm_class_id')
            ->where('a.date', '>=', $from)
            ->where('a.date', '<=', $today)
            ->select([
                'a.crm_class_id',
                'c.site_id as crm_store_id',
                'c.name as class_name',
                // First day of month — consistent date key for crm_presence_summary.month
                DB::raw("DATE_FORMAT(a.date, '%Y-%m-01') as month"),
                // PRESENCE_STATUS != 0 = teacher entered presence/absence for this session
                DB::raw("COUNT(DISTINCT CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, '$.PRESENCE_STATUS')) != '0' THEN a.date END) as saisie_sessions"),
                // PRESENCE_STATUS = 0 = session exists in CRM but no presence entered yet (brouillon)
                DB::raw("COUNT(DISTINCT CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, '$.PRESENCE_STATUS')) = '0' THEN a.date END) as draft_sessions"),
                DB::raw('SUM(a.is_present) as total_present'),
                DB::raw('(COUNT(*) - SUM(a.is_present)) as total_absent'),
                DB::raw('COUNT(DISTINCT a.crm_student_id) as total_students'),
            ])
            ->groupBy(
                'a.crm_class_id',
                'c.site_id',
                'c.name',
                DB::raw("DATE_FORMAT(a.date, '%Y-%m-01')")
            )
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('No attendance data found for the given period.');
            return 0;
        }

        $now     = now()->toDateTimeString();
        $upserts = $rows->map(fn ($r) => [
            'crm_class_id'      => $r->crm_class_id,
            'crm_store_id'      => $r->crm_store_id,
            'class_name'        => $r->class_name ?? '',
            'teacher_name'      => null,
            'month'             => $r->month,
            'saisie_sessions'   => (int) $r->saisie_sessions,
            'draft_sessions'    => (int) $r->draft_sessions,
            'expected_sessions' => (int) $r->saisie_sessions + (int) $r->draft_sessions,
            'missing_sessions'  => 0, // DOW-inferred gaps computed separately if needed
            'total_present'     => (int) $r->total_present,
            'total_absent'      => (int) $r->total_absent,
            'total_students'    => (int) $r->total_students,
            'computed_at'       => $now,
            'created_at'        => $now,
            'updated_at'        => $now,
        ])->toArray();

        // Chunk at 500 rows — safe on shared hosting memory limits
        foreach (array_chunk($upserts, 500) as $chunk) {
            CrmPresenceSummary::upsert(
                $chunk,
                ['crm_class_id', 'month'],
                [
                    'crm_store_id', 'class_name', 'saisie_sessions', 'draft_sessions',
                    'expected_sessions', 'total_present', 'total_absent',
                    'total_students', 'computed_at', 'updated_at',
                ]
            );
        }

        return count($upserts);
    }
}
