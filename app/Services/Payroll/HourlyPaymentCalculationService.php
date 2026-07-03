<?php

namespace App\Services\Payroll;

use App\Models\PresenceImport;
use App\Models\PresencePaymentSummary;

/**
 * Hourly professor payment calculator.
 *
 * final_total = hourly_rate × total_hours
 *
 * This mode has no per-student rows — the professor is paid for their hours,
 * not per student — so it writes only the import + summary totals.
 *
 * NOTE: the performance_bonus column is retained in the DB for historical
 * imports, but is no longer used in the calculation or UI.
 */
class HourlyPaymentCalculationService
{
    /**
     * Recompute the hourly total for an import from its own frozen inputs and
     * refresh both the import's final_total and the payment summary.
     */
    public function calculate(PresenceImport $import): PresencePaymentSummary
    {
        $rate  = (float) ($import->hourly_rate ?? 0);
        $hours = (float) ($import->total_hours ?? 0);

        $finalTotal = $this->computeTotal($rate, $hours);

        $import->update(['final_total' => $finalTotal]);

        return PresencePaymentSummary::updateOrCreate(
            ['presence_import_id' => $import->id],
            [
                'payment_mode'       => PresenceImport::MODE_HOURLY,
                'base_price'         => 0, // not applicable in hourly mode (column is NOT NULL)
                'total_students'     => 0,
                'total_payment'      => $finalTotal,
                'hourly_final_total' => $finalTotal,
            ]
        );
    }

    /**
     * Pure computation — exposed for testing and previews.
     */
    public function computeTotal(float $rate, float $hours): float
    {
        return round($rate * $hours, 2);
    }
}
