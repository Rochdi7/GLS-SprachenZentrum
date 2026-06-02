<?php

namespace App\Services\Payroll;

use App\Models\Group;
use App\Models\HomeschoolSyncLog;
use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Models\PresenceRecord;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeschoolAttendanceService
{
    public function __construct(
        protected Crm $crm,
        protected ProfPaymentCalculationService $calculator
    ) {}

    /**
     * Preview Homeschool API data
     */
    public function preview(Group $group, Carbon $dateStart, Carbon $dateEnd): array
    {
        if (!$group->crm_class_id) {
            throw new \RuntimeException('Group does not have a crm_class_id set');
        }

        $attendanceData = $this->fetchPresenceByDay($group->crm_class_id, $dateStart, $dateEnd);
        $students = collect($attendanceData)
            ->map(fn($row) => $row['studentFullName'] ?? $row['STUDENT_NAME'] ?? $row['NAME'] ?? 'Unknown')
            ->unique()
            ->count();

        $weeksCount = $dateStart->diffInWeeks($dateEnd) + 1;
        $estimatedWeeks = $students * min($weeksCount, 4);

        return [
            'students' => $students,
            'records' => count($attendanceData),
            'weeks' => $estimatedWeeks
        ];
    }

    /**
     * Sync attendance from Homeschool API and calculate professor payment
     */
    public function syncAndCalculate(
        Group $group,
        Carbon $dateStart,
        Carbon $dateEnd,
        ?float $paymentPerStudent = null,
        ?string $notes = null,
        ?int $userId = null
    ): array {
        $syncLog = HomeschoolSyncLog::create([
            'group_id' => $group->id,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'status' => 'pending',
            'created_by' => $userId,
        ]);

        try {
            $result = DB::transaction(function () use ($group, $dateStart, $dateEnd, $paymentPerStudent, $notes, $userId, $syncLog) {
                $parsed = $this->fetchPresenceFromCrm($group, $dateStart, $dateEnd);

                $nextVersion = ($group->presenceImports()->max('version') ?? 0) + 1;

                $import = PresenceImport::create([
                    'group_id' => $group->id,
                    'version' => $nextVersion,
                    'month' => $dateStart->copy()->startOfMonth(),
                    'date_start' => $parsed['date_start'],
                    'date_end' => $parsed['date_end'],
                    'total_days' => $parsed['total_days'],
                    'payment_per_student' => $paymentPerStudent,
                    'notes' => $notes,
                    'imported_by' => $userId,
                    'is_crm_api' => true,
                ]);

                foreach ($parsed['students'] as $studentData) {
                    $student = PresenceImportStudent::create([
                        'presence_import_id' => $import->id,
                        'row_number' => $studentData['row_number'],
                        'student_name' => $studentData['student_name'],
                        'total_present' => $studentData['total_present'],
                        'total_absent' => $studentData['total_absent'],
                        'status' => $studentData['auto_status'] ?? 'active',
                        'crm_student_id' => $studentData['crm_student_id'] ?? null,
                        'raw_data' => $studentData['raw_data'],
                    ]);

                    foreach ($studentData['presence'] as $record) {
                        PresenceRecord::create([
                            'presence_import_student_id' => $student->id,
                            'date' => $record['date'],
                            'status' => $record['status'],
                            'raw_value' => $record['raw_value'],
                        ]);
                    }
                }

                $syncLog->update([
                    'status' => 'success',
                    'records_synced' => count($parsed['students']),
                ]);

                $paymentSummary = $this->calculator->calculate($import);

                return [
                    'import' => $import,
                    'payment_summary' => $paymentSummary,
                    'records_synced' => count($parsed['students']),
                ];
            });

            return $result;
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function fetchPresenceFromCrm(Group $group, Carbon $dateStart, Carbon $dateEnd): array
    {
        $levelSessionId = $group->crm_class_id;

        if (!$levelSessionId) {
            throw new \RuntimeException('Group does not have a crm_class_id set');
        }

        $crmClass = \App\Models\CrmClass::where('crm_id', $levelSessionId)->first();
        $classId = $crmClass?->class_id ?? $levelSessionId;

        $allRows = $this->fetchPresenceByDay($classId, $dateStart, $dateEnd);

        $students = [];
        $dates = [];

        foreach ($allRows as $row) {
            $studentId = $row['STUDENT_ID'] ?? $row['id'] ?? $row['studentId'] ?? null;
            $studentName = $row['STUDENT_NAME'] ?? $row['studentFullName'] ?? $row['NAME'] ?? "Étudiant #{$studentId}";
            $sessionDate = $row['SESSION_DATE'] ?? $row['sessionDate'] ?? $row['date'] ?? null;

            if (!$studentId || !$sessionDate) {
                continue;
            }

            try {
                $dateKey = Carbon::parse($sessionDate)->setTimezone('Africa/Casablanca')->toDateString();
            } catch (\Throwable) {
                continue;
            }

            $dates[$dateKey] = true;
            $status = $this->parsePresenceStatus($row);

            if (!isset($students[$studentId])) {
                $students[$studentId] = [
                    'row_number' => count($students) + 1,
                    'student_name' => $studentName,
                    'crm_student_id' => $studentId,
                    'total_present' => 0,
                    'total_absent' => 0,
                    'auto_status' => 'active',
                    'presence' => [],
                    'raw_data' => $row,
                ];
            }

            $students[$studentId]['presence'][] = [
                'date' => $dateKey,
                'status' => $status,
                'raw_value' => json_encode($row),
            ];

            if ($status === 'present') {
                $students[$studentId]['total_present']++;
            } else {
                $students[$studentId]['total_absent']++;
            }
        }

        ksort($dates);
        $dateList = array_keys($dates);

        foreach ($students as &$student) {
            $presenceByDate = collect($student['presence'])->keyBy('date');
            $completePresence = [];

            foreach ($dateList as $date) {
                if ($presenceByDate->has($date)) {
                    $completePresence[] = $presenceByDate->get($date);
                } else {
                    $completePresence[] = [
                        'date' => $date,
                        'status' => 'absent',
                        'raw_value' => null,
                    ];
                    $student['total_absent']++;
                }
            }

            $student['presence'] = $completePresence;
        }

        return [
            'students' => array_values($students),
            'date_start' => $dateStart->toDateString(),
            'date_end' => $dateEnd->toDateString(),
            'total_days' => count($dateList),
            'date_columns' => $dateList,
        ];
    }

    protected function parsePresenceStatus(array $row): string
    {
        $status = $row['PRESENCE_STATUS'] ?? $row['attendanceStatus'] ?? $row['status'] ?? null;

        if ($status === 1 || $status === '1' || strtolower($status) === 'present') {
            return 'present';
        }

        if (($row['PRESENCE'] ?? $row['present'] ?? null) === 'Y' || ($row['PRESENCE'] ?? $row['present'] ?? null) === 'y') {
            return 'present';
        }

        if ($status === 0 || $status === '0' || strtolower($status) === 'absent') {
            return 'absent';
        }

        if (($row['ABSENCE'] ?? $row['absent'] ?? null) === 'Y' || ($row['ABSENCE'] ?? $row['absent'] ?? null) === 'y') {
            return 'absent';
        }

        return 'absent';
    }

    protected function fetchPresenceByDay(int $classId, Carbon $dateStart, Carbon $dateEnd): array
    {
        try {
            $start = $dateStart->startOfDay();
            $end = $dateEnd->startOfDay();
        } catch (\Throwable) {
            return [];
        }

        if ($end->lt($start)) {
            return [];
        }

        if ($start->diffInDays($end) > 62) {
            return $this->crm->client()->pagedScan(
                path: '/api/external/v1/session-presence',
                baseQuery: ['classId' => $classId],
                pageSize: 25,
                maxPages: 20,
                concurrency: 2,
            );
        }

        $variants = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $variants[] = ['date' => $cursor->toDateString()];
            $cursor->addDay();
        }

        return $this->crm->client()->parallelFetch(
            path: '/api/external/v1/session-presence',
            baseQuery: ['classId' => $classId],
            variantQueries: $variants,
            pageSize: 25,
            concurrency: 3,
        );
    }
}
