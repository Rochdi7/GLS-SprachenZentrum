<?php

namespace App\Services\Reports\Weekly;

use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Models\CrmStudent;
use App\Models\Site;
use Carbon\Carbon;

/**
 * Identifies students with outstanding balances (rest_amount > 0) at end of period.
 */
class WeeklyUnpaidStudentsReportService
{
    /**
     * @return array{
     *   period_label: string,
     *   from: string,
     *   to: string,
     *   total_unpaid_students: int,
     *   total_outstanding_amount: float,
     *   by_center: array,
     *   students: array,
     * }
     */
    public function generate(Carbon $from, Carbon $to): array
    {
        // Resolve most recent snapshot up to $to
        $snapshotDate = CrmPaymentSnapshot::where('snapshot_date', '<=', $to->toDateString())
            ->max('snapshot_date');

        if (!$snapshotDate) {
            return $this->empty($from, $to);
        }

        $unpaidRows = CrmPaymentSnapshot::query()
            ->where('snapshot_date', $snapshotDate)
            ->where('payment_type_id', 1)
            ->where('rest_amount', '>', 0)
            ->get();

        $sites    = Site::all()->keyBy('crm_store_id');
        $students = CrmStudent::whereIn('crm_id', $unpaidRows->pluck('student_id')->unique()->filter())
            ->get()
            ->keyBy('crm_id');

        $studentRows = $unpaidRows
            ->groupBy('student_id')
            ->map(function ($rows, $studentId) use ($students, $sites) {
                $student    = $students->get($studentId);
                $storeId    = $rows->first()?->crm_store_id ?? 0;
                $outstanding = $rows->sum('rest_amount');
                return [
                    'student_id'         => $studentId,
                    'student_name'       => $student?->full_name ?? "Étudiant #{$studentId}",
                    'email'              => $student?->email,
                    'phone'              => $student?->phone,
                    'center_name'        => $sites->get($storeId)?->name ?? "Centre #{$storeId}",
                    'outstanding_amount' => round((float) $outstanding, 2),
                ];
            })
            ->sortByDesc('outstanding_amount')
            ->values()
            ->toArray();

        $byCenter = collect($studentRows)
            ->groupBy('center_name')
            ->map(fn($rows, $name) => [
                'center_name'        => $name,
                'student_count'      => count($rows),
                'outstanding_amount' => round(collect($rows)->sum('outstanding_amount'), 2),
            ])
            ->sortByDesc('outstanding_amount')
            ->values()
            ->toArray();

        return [
            'period_label'            => $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y'),
            'from'                    => $from->toDateString(),
            'to'                      => $to->toDateString(),
            'snapshot_date'           => $snapshotDate,
            'total_unpaid_students'   => count($studentRows),
            'total_outstanding_amount'=> round(collect($studentRows)->sum('outstanding_amount'), 2),
            'by_center'               => $byCenter,
            'students'                => $studentRows,
        ];
    }

    private function empty(Carbon $from, Carbon $to): array
    {
        return [
            'period_label'             => $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y'),
            'from'                     => $from->toDateString(),
            'to'                       => $to->toDateString(),
            'snapshot_date'            => null,
            'total_unpaid_students'    => 0,
            'total_outstanding_amount' => 0.0,
            'by_center'                => [],
            'students'                 => [],
        ];
    }
}
