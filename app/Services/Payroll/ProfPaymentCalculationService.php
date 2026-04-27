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
        $weekMap = $this->buildWeekMap($import->date_start, $import->date_end);

        foreach ($import->students as $student) {
            // Group present dates by ISO week
            $presentByIsoWeek = $student->records
                ->where('status', 'present')
                ->groupBy(fn ($r) => Carbon::parse($r->date)->isoWeek());

            $weekCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($weekMap as $isoWeek => $bucket) {
                if ($presentByIsoWeek->has($isoWeek)) {
                    $weekCounts[$bucket] += $presentByIsoWeek->get($isoWeek)->count();
                }
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
     * Map the ISO week numbers spanned by date_start..date_end onto buckets 1..4.
     * Extra ISO weeks (when month spans 5+) are merged into bucket 4.
     */
    private function buildWeekMap($dateStart, $dateEnd): array
    {
        $start = Carbon::parse($dateStart);
        $end = Carbon::parse($dateEnd);
        $weeks = collect();
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $weeks->push($cursor->isoWeek());
            $cursor->addDay();
        }
        $weekNumbers = $weeks->unique()->values()->all();

        $map = [];
        foreach ($weekNumbers as $idx => $isoWeek) {
            $bucket = min($idx + 1, 4); // 5th+ week merges into bucket 4
            $map[$isoWeek] = $bucket;
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
