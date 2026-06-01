<?php

namespace App\Services\Payroll;

use App\Models\Group;
use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Models\PresenceRecord;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates CRM API-powered presence import for professor payment calculation.
 *
 * Instead of uploading Excel, fetches presence data directly from Homeschool CRM API
 * using /api/external/v1/session-presence endpoint.
 */
class CrmPresenceImportService
{
    public function __construct(
        protected ProfPaymentCalculationService $calculator,
    ) {}

    /**
     * Import presence data from CRM API for a group and date range.
     */
    public function importFromCrm(
        Group $group,
        Crm $crm,
        Carbon $dateStart,
        Carbon $dateEnd,
        ?float $paymentPerStudent = null,
        ?string $notes = null,
        ?int $importedBy = null,
    ): PresenceImport {
        return DB::transaction(function () use ($group, $crm, $dateStart, $dateEnd, $paymentPerStudent, $notes, $importedBy) {

            $nextVersion = ($group->presenceImports()->max('version') ?? 0) + 1;

            $parsed = $this->fetchPresenceFromCrm(
                crm: $crm,
                group: $group,
                dateStart: $dateStart,
                dateEnd: $dateEnd,
            );

            if (empty($parsed['students'])) {
                throw new \RuntimeException(
                    'Aucune donnée de présence trouvée dans le CRM pour cette période.'
                );
            }

            $import = PresenceImport::create([
                'group_id' => $group->id,
                'version' => $nextVersion,
                'month' => $dateStart->copy()->startOfMonth(),
                'date_start' => $parsed['date_start'],
                'date_end' => $parsed['date_end'],
                'total_days' => $parsed['total_days'],
                'payment_per_student' => $paymentPerStudent,
                'file_name' => 'CRM_API_IMPORT',
                'file_path' => 'crm_api_import',
                'notes' => $notes,
                'imported_by' => $importedBy,
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

            $this->calculator->calculate($import);

            return $import;
        });
    }

    /**
     * Fetch presence data from CRM API and structure it like Excel parser output.
     */
    protected function fetchPresenceFromCrm(
        Crm $crm,
        Group $group,
        Carbon $dateStart,
        Carbon $dateEnd,
    ): array {
        $classId = $group->crm_class_id;

        if (!$classId) {
            throw new \RuntimeException('Groupe non lié à une classe CRM. Veuillez configurer crm_class_id.');
        }

        $allRows = $this->fetchPresenceByDay(
            crm: $crm,
            classId: $classId,
            startDate: $dateStart->toDateString(),
            endDate: $dateEnd->toDateString(),
        );

        $students = [];
        $dates = [];

        foreach ($allRows as $row) {
            $studentId = $row['STUDENT_ID'] ?? $row['ID'] ?? null;
            $studentName = $row['STUDENT_NAME'] ?? $row['FULL_NAME'] ?? $row['NAME'] ?? "Étudiant #{$studentId}";
            $sessionDate = $row['SESSION_DATE'] ?? null;

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

    /**
     * Parse presence status from CRM API row.
     */
    protected function parsePresenceStatus(array $row): string
    {
        $status = $row['PRESENCE_STATUS'] ?? null;

        if ($status === 1 || $status === '1' || $status === 'PRESENT' || $status === 'present') {
            return 'present';
        }

        if (($row['PRESENCE'] ?? null) === 'Y' || ($row['PRESENCE'] ?? null) === 'y') {
            return 'present';
        }

        if ($status === 0 || $status === '0' || $status === 'ABSENT' || $status === 'absent') {
            return 'absent';
        }

        if (($row['ABSENCE'] ?? null) === 'Y' || ($row['ABSENCE'] ?? null) === 'y') {
            return 'absent';
        }

        return 'absent';
    }

    /**
     * Fetch session-presence rows day-by-day in parallel from CRM API.
     */
    protected function fetchPresenceByDay(
        Crm $crm,
        int $classId,
        string $startDate,
        string $endDate,
    ): array {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();
        } catch (\Throwable) {
            return [];
        }

        if ($end->lt($start)) {
            return [];
        }

        if ($start->diffInDays($end) > 62) {
            return $crm->client()->pagedScan(
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

        return $crm->client()->parallelFetch(
            path: '/api/external/v1/session-presence',
            baseQuery: ['classId' => $classId],
            variantQueries: $variants,
            pageSize: 50,
            concurrency: 3,
        );
    }
}
