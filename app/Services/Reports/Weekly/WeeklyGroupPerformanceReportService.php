<?php

namespace App\Services\Reports\Weekly;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\CrmRegistration;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Rates each group by attendance rate + new registrations during the week.
 */
class WeeklyGroupPerformanceReportService
{
    /**
     * @return array{
     *   period_label: string,
     *   from: string,
     *   to: string,
     *   total_active_groups: int,
     *   total_new_registrations: int,
     *   groups: array,
     * }
     */
    public function generate(Carbon $from, Carbon $to): array
    {
        $attendance = CrmAttendance::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $newRegistrations = CrmRegistration::query()
            ->whereBetween('date_creation', [$from->toDateString(), $to->toDateString()])
            ->get();

        $classIds = $attendance->pluck('crm_class_id')->merge($newRegistrations->pluck('crm_class_id'))
            ->unique()->filter()->values();

        $classes = CrmClass::whereIn('crm_id', $classIds)->get()->keyBy('crm_id');
        $sites   = Site::all()->keyBy('crm_store_id');

        $attendanceByClass = $attendance->groupBy('crm_class_id');
        $regByClass        = $newRegistrations->groupBy('crm_class_id');

        $groups = $classIds->map(function (int $classId) use ($classes, $sites, $attendanceByClass, $regByClass) {
            $class   = $classes->get($classId);
            $storeId = $class?->crm_store_id ?? 0;

            $attRows = $attendanceByClass->get($classId, collect());
            $present = $attRows->where('is_present', true)->count();
            $total   = $attRows->count();
            $rate    = $total > 0 ? round($present / $total * 100, 1) : null;

            $newRegs = $regByClass->get($classId, collect())->count();

            return [
                'class_id'           => $classId,
                'class_name'         => $class?->name ?? "Classe #{$classId}",
                'center_name'        => $sites->get($storeId)?->name ?? "Centre #{$storeId}",
                'level'              => $class?->level ?? '—',
                'sessions_held'      => $total > 0 ? $attRows->pluck('date')->unique()->count() : 0,
                'attendance_rate'    => $rate,
                'present'            => $present,
                'absent'             => $total - $present,
                'new_registrations'  => $newRegs,
            ];
        })->sortByDesc('attendance_rate')->values()->toArray();

        return [
            'period_label'           => $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y'),
            'from'                   => $from->toDateString(),
            'to'                     => $to->toDateString(),
            'total_active_groups'    => count($groups),
            'total_new_registrations'=> $newRegistrations->count(),
            'groups'                 => $groups,
        ];
    }
}
