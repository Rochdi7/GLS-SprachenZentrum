<?php

namespace App\Services\Reports\Weekly;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Aggregates key KPIs per center for the week:
 *  - Revenue collected (cash, payment_type_id = 1)
 *  - New registrations
 *  - Attendance rate
 */
class WeeklyCenterPerformanceReportService
{
    /**
     * @return array{
     *   period_label: string,
     *   from: string,
     *   to: string,
     *   total_revenue: float,
     *   total_new_registrations: int,
     *   centers: array,
     * }
     */
    public function generate(Carbon $from, Carbon $to): array
    {
        // Revenue: payments with date_creation in range, using latest available snapshot
        $snapshotDate = CrmPaymentSnapshot::where('snapshot_date', '<=', $to->toDateString())
            ->max('snapshot_date');

        $revenueByStore = collect();
        if ($snapshotDate) {
            $revenueByStore = CrmPaymentSnapshot::query()
                ->where('snapshot_date', $snapshotDate)
                ->where('payment_type_id', 1)
                ->whereBetween('date_creation_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('crm_store_id, SUM(amount) as total_revenue')
                ->groupBy('crm_store_id')
                ->get()
                ->keyBy('crm_store_id');
        }

        // Registrations
        $regByStore = CrmRegistration::query()
            ->whereBetween('date_creation', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('crm_store_id, COUNT(*) as count')
            ->groupBy('crm_store_id')
            ->get()
            ->keyBy('crm_store_id');

        // Attendance
        $attendance = CrmAttendance::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $classes         = CrmClass::whereIn('crm_id', $attendance->pluck('crm_class_id')->unique())
            ->get()
            ->keyBy('crm_id');
        $attendByStore   = $attendance
            ->groupBy(fn($row) => $classes->get($row->crm_class_id)?->crm_store_id ?? 0);

        $sites = Site::all();

        $centers = $sites->map(function (Site $site) use ($revenueByStore, $regByStore, $attendByStore) {
            $storeId  = $site->crm_store_id;
            $revenue  = (float) ($revenueByStore->get($storeId)?->total_revenue ?? 0);
            $newRegs  = (int) ($regByStore->get($storeId)?->count ?? 0);

            $attRows  = $attendByStore->get($storeId, collect());
            $present  = $attRows->where('is_present', true)->count();
            $total    = $attRows->count();
            $rate     = $total > 0 ? round($present / $total * 100, 1) : null;

            return [
                'center_name'        => $site->name,
                'crm_store_id'       => $storeId,
                'revenue'            => round($revenue, 2),
                'new_registrations'  => $newRegs,
                'attendance_rate'    => $rate,
                'sessions_total'     => $total,
            ];
        })->sortByDesc('revenue')->values()->toArray();

        return [
            'period_label'           => $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y'),
            'from'                   => $from->toDateString(),
            'to'                     => $to->toDateString(),
            'total_revenue'          => round(collect($centers)->sum('revenue'), 2),
            'total_new_registrations'=> collect($centers)->sum('new_registrations'),
            'centers'                => $centers,
        ];
    }
}
