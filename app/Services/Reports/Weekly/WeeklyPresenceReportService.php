<?php

namespace App\Services\Reports\Weekly;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Generates weekly attendance/presence data aggregated by class and center.
 */
class WeeklyPresenceReportService
{
    /**
     * @return array{
     *   period_label: string,
     *   from: string,
     *   to: string,
     *   total_sessions: int,
     *   total_present: int,
     *   total_absent: int,
     *   attendance_rate: float,
     *   by_center: array,
     *   by_class: array,
     * }
     */
    public function generate(Carbon $from, Carbon $to): array
    {
        $attendance = CrmAttendance::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $totalPresent = $attendance->where('is_present', true)->count();
        $totalAbsent  = $attendance->where('is_present', false)->count();
        $total        = $attendance->count();
        $rate         = $total > 0 ? round($totalPresent / $total * 100, 1) : 0.0;

        $classes = CrmClass::whereIn('crm_id', $attendance->pluck('crm_class_id')->unique()->values())
            ->get()
            ->keyBy('crm_id');

        $sites = Site::all()->keyBy('crm_store_id');

        $byClass = $attendance
            ->groupBy('crm_class_id')
            ->map(function (Collection $rows, int $classId) use ($classes) {
                $class   = $classes->get($classId);
                $present = $rows->where('is_present', true)->count();
                $total   = $rows->count();
                return [
                    'class_name'      => $class?->name ?? "Classe #{$classId}",
                    'present'         => $present,
                    'absent'          => $total - $present,
                    'total'           => $total,
                    'attendance_rate' => $total > 0 ? round($present / $total * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('attendance_rate')
            ->values()
            ->toArray();

        $byCenter = $attendance
            ->groupBy(fn($row) => $classes->get($row->crm_class_id)?->crm_store_id ?? 0)
            ->map(function (Collection $rows, $storeId) use ($sites) {
                $present = $rows->where('is_present', true)->count();
                $total   = $rows->count();
                return [
                    'center_name'     => $sites->get($storeId)?->name ?? "Centre #{$storeId}",
                    'present'         => $present,
                    'absent'          => $total - $present,
                    'total'           => $total,
                    'attendance_rate' => $total > 0 ? round($present / $total * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('attendance_rate')
            ->values()
            ->toArray();

        return [
            'period_label'    => $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y'),
            'from'            => $from->toDateString(),
            'to'              => $to->toDateString(),
            'total_sessions'  => $total,
            'total_present'   => $totalPresent,
            'total_absent'    => $totalAbsent,
            'attendance_rate' => $rate,
            'by_center'       => $byCenter,
            'by_class'        => $byClass,
        ];
    }
}
