<?php

namespace App\Services\Payroll;

use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Models\PresencePaymentSummary;
use App\Models\SystemConfig;

/**
 * Period / month-based professor payment calculator.
 *
 * Unlike the legacy weekly bucket model (ProfPaymentCalculationService — which
 * this class NEVER touches), payment here is driven by each student's TOTAL
 * presences across the whole selected period, mapped onto configurable tiers.
 *
 * For each student:
 *   presences → tier → weeks equivalent → weeks × (base_price / weeks_per_period)
 *   below the lowest tier's `min` → 0 DH
 *
 * Reproducibility guarantee:
 *   The tier table is FROZEN inside the import (period_tiers_json) at creation.
 *   Recalculation always reads the import's frozen tiers — never live config —
 *   so historical payroll can never drift when the global default is edited.
 *
 * Manual override:
 *   period_amount_override wins over the auto amount. Overrides carry an audit
 *   trail (override_reason / override_by / override_at) set by the controller.
 */
class PeriodPaymentCalculationService
{
    /** Hard fallback if an import somehow has no frozen tiers (defensive only). */
    public const FALLBACK_TIERS = [
        ['min' => 5,  'max' => 6,    'weeks' => 1],
        ['min' => 7,  'max' => 10,   'weeks' => 2],
        ['min' => 11, 'max' => null, 'weeks' => 'full'],
    ];

    public const FALLBACK_WEEKS_PER_PERIOD = 4;

    /**
     * Snapshot the current global tier config so it can be frozen onto a new
     * import at creation time. Call this from the controller when creating a
     * period import, then store the result in period_tiers_json.
     */
    public static function currentTiersSnapshot(): array
    {
        $raw = SystemConfig::get('period.tiers');
        $tiers = is_string($raw) ? json_decode($raw, true) : $raw;

        return self::sanitizeTiers(is_array($tiers) ? $tiers : self::FALLBACK_TIERS);
    }

    public static function currentWeeksPerPeriod(): int
    {
        $n = (int) SystemConfig::get('period.weeks_per_period', self::FALLBACK_WEEKS_PER_PERIOD);

        return $n > 0 ? $n : self::FALLBACK_WEEKS_PER_PERIOD;
    }

    /**
     * Recompute every student's period payment for the given import and refresh
     * the payment summary. Reads FROZEN tiers from the import only.
     */
    public function calculate(PresenceImport $import): PresencePaymentSummary
    {
        $basePrice      = (float) ($import->base_price ?? $import->getEffectivePaymentPerStudent() ?? 0);
        $weeksPerPeriod = $import->getWeeksPerPeriod();
        $tiers          = $import->getFrozenTiers();
        $unitAmount     = $weeksPerPeriod > 0 ? round($basePrice / $weeksPerPeriod, 2) : 0.0;

        $import->load('students');

        $totalPayment   = 0.0;
        $activeStudents = 0;
        $countFull      = 0;
        $countPartial   = 0;
        $countZero      = 0;

        foreach ($import->students as $student) {
            $presences = $this->resolvePresenceCount($student);

            [$weeks, $auto] = $this->amountFor($presences, $tiers, $basePrice, $unitAmount, $weeksPerPeriod);

            $override  = $student->period_amount_override;
            $effective = $override !== null ? (float) $override : $auto;

            $student->update([
                'period_presence_count' => $presences,
                'period_auto_amount'    => $auto,
                // weighted_amount kept in sync so shared displays / exports work
                'weighted_amount'       => round($effective, 2),
            ]);

            $totalPayment += $effective;

            if ($effective > 0) {
                $activeStudents++;
            }

            // Tier bucket counts for the summary (based on AUTO tier, not override)
            if ($weeks === 'full') {
                $countFull++;
            } elseif ($auto > 0) {
                $countPartial++;
            } else {
                $countZero++;
            }
        }

        return PresencePaymentSummary::updateOrCreate(
            ['presence_import_id' => $import->id],
            [
                'payment_mode'       => PresenceImport::MODE_PERIOD,
                'base_price'         => $basePrice,
                'period_unit_amount' => $unitAmount,
                'count_tier_full'    => $countFull,
                'count_tier_partial' => $countPartial,
                'count_tier_zero'    => $countZero,
                'total_students'     => $activeStudents,
                'total_payment'      => round($totalPayment, 2),
            ]
        );
    }

    /**
     * Map a presence count onto its tier and return [weeksLabel, amount].
     *
     * @return array{0: int|string, 1: float}
     */
    public function amountFor(int $presences, array $tiers, float $basePrice, float $unitAmount, int $weeksPerPeriod): array
    {
        foreach ($tiers as $tier) {
            $min = (int) ($tier['min'] ?? 0);
            $max = $tier['max'] ?? null; // null = open-ended upper bound

            $inRange = $presences >= $min && ($max === null || $presences <= (int) $max);
            if (! $inRange) {
                continue;
            }

            $weeks = $tier['weeks'] ?? 0;

            if ($weeks === 'full') {
                return ['full', round($basePrice, 2)];
            }

            $w = (int) $weeks;
            // Never pay more than the full base price via week multiplication
            $amount = min($w * $unitAmount, $basePrice);

            return [$w, round($amount, 2)];
        }

        // Below the lowest tier → nothing earned
        return [0, 0.0];
    }

    /**
     * Total presences for a student. Prefer the stored total_present; fall back
     * to counting present records if needed.
     */
    protected function resolvePresenceCount(PresenceImportStudent $student): int
    {
        if ($student->total_present !== null) {
            return (int) $student->total_present;
        }

        return (int) $student->records()->where('status', 'present')->count();
    }

    /**
     * Normalise a tier array: keep only valid rows, sort ascending by min.
     */
    protected static function sanitizeTiers(array $tiers): array
    {
        $clean = [];
        foreach ($tiers as $t) {
            if (! isset($t['min'])) {
                continue;
            }
            $clean[] = [
                'min'   => (int) $t['min'],
                'max'   => (isset($t['max']) && $t['max'] !== null && $t['max'] !== '') ? (int) $t['max'] : null,
                'weeks' => ($t['weeks'] ?? 0) === 'full' ? 'full' : (int) ($t['weeks'] ?? 0),
            ];
        }

        usort($clean, fn ($a, $b) => $a['min'] <=> $b['min']);

        return $clean ?: self::FALLBACK_TIERS;
    }
}
