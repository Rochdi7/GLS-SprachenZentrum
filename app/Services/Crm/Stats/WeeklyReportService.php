<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmDailyReport;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Models\CrmWeeklyReport;
use App\Models\Site;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class WeeklyReportService
{
    /**
     * Build and persist the weekly CEO report for the week containing $anchorDate.
     * Defaults to the last completed week (Mon–Sun ending last Sunday).
     */
    public function generate(?string $anchorDate = null): CrmWeeklyReport
    {
        $anchor = $anchorDate
            ? Carbon::parse($anchorDate, 'Africa/Casablanca')
            : Carbon::now('Africa/Casablanca')->subWeek();

        $weekStart = CrmWeeklyReport::weekStartFor($anchor);
        $weekEnd   = $weekStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $from = $weekStart->toDateString();
        $to   = $weekEnd->toDateString();

        $data = $this->buildPayload($from, $to, $weekStart);

        return CrmWeeklyReport::updateOrCreate(
            ['week_start' => $from],
            $data
        );
    }

    // -----------------------------------------------------------------------

    private function buildPayload(string $from, string $to, Carbon $weekStart): array
    {
        $dailyBreakdown = $this->dailyBreakdown($from, $to);
        $totalRevenue   = array_sum(array_column($dailyBreakdown, 'revenue'));
        $totalRegs      = array_sum(array_column($dailyBreakdown, 'registrations'));
        $centersRanking = $this->centersRanking($from, $to);
        $bestCenter     = $centersRanking[0]['name'] ?? null;

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $weekNum = $weekStart->isoWeek();

        return [
            'week_start'        => $from,
            'week_end'          => $to,
            'week_label'        => "S{$weekNum} — " . $weekStart->format('d/m') . ' au ' . $weekEnd->format('d/m/Y'),
            'total_revenue'     => $totalRevenue,
            'new_registrations' => $totalRegs,
            'best_center'       => $bestCenter,
            'centers_ranking'   => $centersRanking,
            'daily_breakdown'   => $dailyBreakdown,
            'payload'           => [
                'from'            => $from,
                'to'              => $to,
                'daily_breakdown' => $dailyBreakdown,
                'centers_ranking' => $centersRanking,
            ],
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    private function dailyBreakdown(string $from, string $to): array
    {
        $days = [];
        foreach (CarbonPeriod::create($from, $to) as $day) {
            $date     = $day->toDateString();
            $snapshot = $this->resolveSnapshotDate($date);

            $revenue = $snapshot
                ? (float) CrmPaymentSnapshot::query()
                    ->where('snapshot_date', $snapshot)
                    ->where('payment_type_id', 1)
                    ->whereDate('date_creation', $date)
                    ->sum('amount')
                : 0.0;

            $regs = CrmRegistration::query()
                ->whereDate('date_creation', $date)
                ->whereNotNull('date_creation')
                ->count();

            $days[] = [
                'date'          => $date,
                'day_label'     => $day->locale('fr')->isoFormat('ddd D/MM'),
                'revenue'       => $revenue,
                'registrations' => $regs,
            ];
        }
        return $days;
    }

    private function centersRanking(string $from, string $to): array
    {
        $snapshotDates = CrmPaymentSnapshot::whereBetween('snapshot_date', [$from, $to])
            ->distinct()
            ->pluck('snapshot_date')
            ->toArray();

        if (empty($snapshotDates)) return [];

        $rows = CrmPaymentSnapshot::query()
            ->whereIn('snapshot_date', $snapshotDates)
            ->where('payment_type_id', 1)
            ->whereBetween('date_creation', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('crm_store_id, SUM(amount) as total')
            ->groupBy('crm_store_id')
            ->orderByDesc('total')
            ->get();

        $sites = Site::whereNotNull('crm_store_id')->get()->keyBy('crm_store_id');

        $ranking = $rows->map(fn($row) => [
            'name'   => $sites->get($row->crm_store_id)?->name ?? "Store #{$row->crm_store_id}",
            'amount' => (float) $row->total,
        ])->values()->toArray();

        $rankedIds = $rows->pluck('crm_store_id')->toArray();
        foreach ($sites as $storeId => $site) {
            if (!in_array($storeId, $rankedIds)) {
                $ranking[] = ['name' => $site->name, 'amount' => 0.0];
            }
        }

        return $ranking;
    }

    private function resolveSnapshotDate(string $date): ?string
    {
        return CrmPaymentSnapshot::where('snapshot_date', $date)->exists()
            ? $date
            : CrmPaymentSnapshot::where('snapshot_date', '>', $date)->min('snapshot_date')
            ?? CrmPaymentSnapshot::max('snapshot_date');
    }
}
