<?php

namespace App\Services\Reports\Monthly;

use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Models\Site;
use Carbon\Carbon;

/**
 * Generates monthly revenue summary from payment snapshots.
 * Strictly one calendar month — use ReportPeriodResolver::singleMonth().
 */
class MonthlyRevenueReportService
{
    /**
     * @return array{
     *   period_label: string,
     *   month_label: string,
     *   from: string,
     *   to: string,
     *   total_revenue: float,
     *   total_registrations: int,
     *   avg_daily_revenue: float,
     *   by_center: array,
     *   daily_breakdown: array,
     * }
     */
    public function generate(Carbon $from, Carbon $to): array
    {
        // Use the snapshot closest to end-of-month
        $snapshotDate = CrmPaymentSnapshot::where('snapshot_date', '<=', $to->toDateString())
            ->max('snapshot_date');

        $byCenter  = [];
        $daily     = [];
        $total     = 0.0;

        if ($snapshotDate) {
            $payments = CrmPaymentSnapshot::query()
                ->where('snapshot_date', $snapshotDate)
                ->where('payment_type_id', 1)
                ->whereBetween('date_creation_date', [$from->toDateString(), $to->toDateString()])
                ->get();

            $sites = Site::all()->keyBy('crm_store_id');

            $byCenter = $payments
                ->groupBy('crm_store_id')
                ->map(fn($rows, $storeId) => [
                    'center_name' => $sites->get($storeId)?->name ?? "Centre #{$storeId}",
                    'revenue'     => round((float) $rows->sum('amount'), 2),
                    'payments'    => $rows->count(),
                ])
                ->sortByDesc('revenue')
                ->values()
                ->toArray();

            $daily = $payments
                ->groupBy('date_creation_date')
                ->map(fn($rows, $date) => [
                    'date'    => $date,
                    'revenue' => round((float) $rows->sum('amount'), 2),
                    'count'   => $rows->count(),
                ])
                ->sortBy('date')
                ->values()
                ->toArray();

            $total = round((float) $payments->sum('amount'), 2);
        }

        $days      = $from->diffInDays($to) + 1;
        $newRegs   = CrmRegistration::whereBetween('date_creation', [$from->toDateString(), $to->toDateString()])->count();

        return [
            'period_label'       => $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y'),
            'month_label'        => $from->translatedFormat('F Y'),
            'from'               => $from->toDateString(),
            'to'                 => $to->toDateString(),
            'snapshot_date'      => $snapshotDate,
            'total_revenue'      => $total,
            'total_registrations'=> $newRegs,
            'avg_daily_revenue'  => $days > 0 ? round($total / $days, 2) : 0.0,
            'by_center'          => $byCenter,
            'daily_breakdown'    => $daily,
        ];
    }
}
