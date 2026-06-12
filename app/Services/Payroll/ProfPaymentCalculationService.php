<?php

namespace App\Services\Payroll;

use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Models\PresencePaymentSummary;
use Carbon\Carbon;

/**
 * Per-week flat-rate professor payment calculator.
 *
 * For each student × each of the 4 weeks:
 *   - count present days in that week
 *   - if count >= weekly_threshold (default 3) → student-week earns
 *     base_price × weekly_rate_percent (default 25% → e.g. 500 → 125 DH)
 *   - else 0 DH
 * Per-week amounts are stored on the student row and can be manually
 * overridden by a responsable (week_N_amount_override).
 *
 * Total prof payment = sum of effective amounts across all student-weeks.
 */
class ProfPaymentCalculationService
{
    public function calculate(PresenceImport $import): PresencePaymentSummary
    {
        $basePrice = (float) ($import->getEffectivePaymentPerStudent() ?? 0);
        $threshold = $import->getThreshold();
        $unitAmount = $import->getWeeklyUnitAmount();

        $totalPayment = 0.0;
        $qualifiedWeeks = 0;
        $unqualifiedWeeks = 0;
        $totalActiveStudents = 0;

        $import->load('students.records');

        // Build the canonical 4-week buckets for this import period
        $weekMap = $this->buildWeekMap($import, $threshold);

        foreach ($import->students as $student) {
            // Group present dates by ISO week
            $presentByIsoWeek = $student->records
                ->where('status', 'present')
                ->groupBy(fn ($r) => Carbon::parse($r->date)->isoFormat('GGGG-WW'));

            // Per-bucket counts, with each ISO week capped at 5 working days
            // (Mon-Fri only — the school doesn't operate weekends, so a week
            //  can never legitimately have more than 5 presences). When the
            //  month spills over into a 5th ISO week, those days are folded
            //  into bucket 4 but the cap prevents the total from exceeding 5.
            $weekCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($weekMap as $isoWeek => $bucket) {
                if ($presentByIsoWeek->has($isoWeek)) {
                    $weekCounts[$bucket] += $presentByIsoWeek->get($isoWeek)->count();
                }
            }
            foreach ($weekCounts as $b => $c) {
                $weekCounts[$b] = min($c, 5);
            }

            $isInactive = ($student->isCancelled() || $student->isTransferred())
                && $student->records->where('status', 'present')->isEmpty();

            $updates = [];
            $studentTotal = 0.0;
            $studentQualified = false;

            foreach ([1, 2, 3, 4] as $w) {
                $count = $isInactive ? 0 : $weekCounts[$w];
                $auto = ($count >= $threshold) ? $unitAmount : 0.0;
                $override = $student->{"week_{$w}_amount_override"};
                $effective = $override !== null ? (float) $override : $auto;

                $updates["week_{$w}_presence"] = $count;
                $updates["week_{$w}_amount"] = $auto;

                if ($count >= $threshold) {
                    $qualifiedWeeks++;
                    $studentQualified = true;
                } else {
                    $unqualifiedWeeks++;
                }

                $studentTotal += $effective;
            }

            // Keep legacy fields populated so old views/exports still render
            $activeQuarters = collect($weekCounts)->filter(fn ($c) => $c > 0)->count();
            $updates['active_quarters'] = $activeQuarters;
            $updates['category'] = $this->mapQuartersToLegacyCategory($activeQuarters);
            $updates['weighted_amount'] = round($studentTotal, 2);

            $student->update($updates);

            $totalPayment += $studentTotal;
            if ($studentQualified) {
                $totalActiveStudents++;
            }
        }

        // Legacy category counts (still useful for at-a-glance breakdowns)
        $counts = $this->countLegacyCategories($import);

        return PresencePaymentSummary::updateOrCreate(
            ['presence_import_id' => $import->id],
            [
                'base_price' => $basePrice,
                'weekly_unit_amount' => $unitAmount,
                'count_full' => $counts['full'],
                'count_three_quarter' => $counts['three_quarter'],
                'count_half' => $counts['half'],
                'count_quarter' => $counts['quarter'],
                'count_zero' => $counts['zero'],
                'count_qualified_weeks' => $qualifiedWeeks,
                'count_unqualified_weeks' => $unqualifiedWeeks,
                'total_students' => $totalActiveStudents,
                'total_payment' => round($totalPayment, 2),
            ]
        );
    }

    /**
     * Override (or clear) a single student-week amount, then recompute totals.
     */
    public function overrideStudentWeek(PresenceImportStudent $student, int $week, ?float $amount): void
    {
        if (! in_array($week, [1, 2, 3, 4], true)) {
            throw new \InvalidArgumentException("Week must be 1-4, got {$week}");
        }

        $student->update(["week_{$week}_amount_override" => $amount]);

        $import = $student->presenceImport;
        $this->calculate($import);
    }

    /**
     * Map ISO weeks (keyed "GGGG-WW") onto buckets 1..4, based on the days
     * that actually have attendance records — not the raw calendar.
     *
     * A week with fewer course days than the threshold can never qualify on
     * its own (e.g. a holiday week reduced to a single Monday), so instead of
     * consuming one of the 4 SEM slots and making it unwinnable, it shares a
     * bucket with the following week. Extra weeks beyond 4 buckets are merged
     * into bucket 4, and a trailing bucket too short to qualify is merged
     * into the previous one.
     */
    private function buildWeekMap(PresenceImport $import, int $threshold): array
    {
        // Distinct course dates = every date that has at least one record
        // (present or absent), chronologically ordered
        $courseDates = $import->students
            ->flatMap(fn ($s) => $s->records->pluck('date'))
            ->map(fn ($d) => Carbon::parse($d))
            ->unique(fn ($d) => $d->toDateString())
            ->sortBy(fn ($d) => $d->getTimestamp())
            ->values();

        // Course-day count per ISO week, in chronological order
        $dayCounts = [];
        foreach ($courseDates as $d) {
            $key = $d->isoFormat('GGGG-WW');
            $dayCounts[$key] = ($dayCounts[$key] ?? 0) + 1;
        }

        $map = [];
        $bucket = 0;
        $openDays = 0; // course days accumulated in the current bucket
        foreach ($dayCounts as $key => $count) {
            // Open a new bucket only once the current one has enough course
            // days to be qualifiable; otherwise this week joins it.
            if ($bucket === 0 || ($openDays >= $threshold && $bucket < 4)) {
                $bucket++;
                $openDays = 0;
            }
            $map[$key] = $bucket;
            $openDays += $count;
        }

        // A trailing bucket too short to ever qualify merges into the previous one
        if ($bucket > 1 && $openDays < $threshold) {
            foreach ($map as $key => $b) {
                if ($b === $bucket) {
                    $map[$key] = $bucket - 1;
                }
            }
        }

        return $map;
    }

    private function mapQuartersToLegacyCategory(int $activeQuarters): string
    {
        return match ($activeQuarters) {
            4 => PresenceImportStudent::CATEGORY_FULL,
            3 => PresenceImportStudent::CATEGORY_THREE_QUARTER,
            2 => PresenceImportStudent::CATEGORY_HALF,
            1 => PresenceImportStudent::CATEGORY_QUARTER,
            default => PresenceImportStudent::CATEGORY_ZERO,
        };
    }

    private function countLegacyCategories(PresenceImport $import): array
    {
        $counts = ['full' => 0, 'three_quarter' => 0, 'half' => 0, 'quarter' => 0, 'zero' => 0];
        foreach ($import->students()->get() as $student) {
            $counts[$student->category] = ($counts[$student->category] ?? 0) + 1;
        }

        return $counts;
    }
}
