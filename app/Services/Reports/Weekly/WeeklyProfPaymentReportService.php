<?php

namespace App\Services\Reports\Weekly;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Estimates professor payment for the week based on sessions taught.
 *
 * Payment logic: number of sessions with at least one present student × hourly rate.
 * Hourly rate falls back to config default when not stored on the class.
 */
class WeeklyProfPaymentReportService
{
    // Default hourly rate in MAD when not configured per teacher.
    protected float $defaultHourlyRate;

    public function __construct()
    {
        $this->defaultHourlyRate = (float) config('reports.default_hourly_rate', 80.0);
    }

    /**
     * @return array{
     *   period_label: string,
     *   from: string,
     *   to: string,
     *   total_estimated_payment: float,
     *   total_sessions: int,
     *   by_teacher: array,
     * }
     */
    public function generate(Carbon $from, Carbon $to): array
    {
        $attendance = CrmAttendance::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where('is_present', true)
            ->get();

        $classes = CrmClass::whereIn('crm_id', $attendance->pluck('crm_class_id')->unique()->values())
            ->get()
            ->keyBy('crm_id');

        $sites = Site::all()->keyBy('crm_store_id');

        // Group sessions by teacher (using crm_teacher_id from CrmClass)
        $byTeacher = $attendance
            ->groupBy('crm_class_id')
            ->map(function (Collection $rows, int $classId) use ($classes, $sites) {
                $class      = $classes->get($classId);
                $teacherId  = $class?->crm_teacher_id ?? 'unknown';
                $teacherName = $class?->raw_data['teacher_full_name']
                    ?? $class?->raw_data['teacher_name']
                    ?? "Prof #{$teacherId}";
                $storeId    = $class?->crm_store_id ?? 0;

                // Count unique session dates (one session = one day × one class)
                $sessionDates = $rows->pluck('date')->unique()->count();

                return [
                    'teacher_id'        => $teacherId,
                    'teacher_name'      => $teacherName,
                    'center_name'       => $sites->get($storeId)?->name ?? "Centre #{$storeId}",
                    'class_name'        => $class?->name ?? "Classe #{$classId}",
                    'sessions_taught'   => $sessionDates,
                    'hourly_rate'       => $this->defaultHourlyRate,
                    'estimated_payment' => round($sessionDates * $this->defaultHourlyRate, 2),
                ];
            })
            ->values()
            ->groupBy('teacher_id')
            ->map(function (Collection $classRows) {
                $first = $classRows->first();
                return [
                    'teacher_name'      => $first['teacher_name'],
                    'center_name'       => $first['center_name'],
                    'sessions_taught'   => $classRows->sum('sessions_taught'),
                    'hourly_rate'       => $first['hourly_rate'],
                    'estimated_payment' => $classRows->sum('estimated_payment'),
                    'classes'           => $classRows->pluck('class_name')->toArray(),
                ];
            })
            ->sortByDesc('estimated_payment')
            ->values()
            ->toArray();

        $totalPayment  = collect($byTeacher)->sum('estimated_payment');
        $totalSessions = collect($byTeacher)->sum('sessions_taught');

        return [
            'period_label'            => $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y'),
            'from'                    => $from->toDateString(),
            'to'                      => $to->toDateString(),
            'total_estimated_payment' => round($totalPayment, 2),
            'total_sessions'          => $totalSessions,
            'default_hourly_rate'     => $this->defaultHourlyRate,
            'by_teacher'              => $byTeacher,
        ];
    }
}
