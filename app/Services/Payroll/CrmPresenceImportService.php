<?php

namespace App\Services\Payroll;

use App\Models\CrmAttendance;
use App\Models\CrmStudent;
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
        ?string $monthLabel = null,
        ?string $crmTeacherName = null,
    ): PresenceImport {
        return DB::transaction(function () use ($group, $crm, $dateStart, $dateEnd, $paymentPerStudent, $notes, $importedBy, $monthLabel, $crmTeacherName) {

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
                'file_name'        => 'CRM_API_IMPORT',
                'file_path'        => 'crm_api_import',
                'notes'            => $notes,
                'imported_by'      => $importedBy,
                'is_crm_api'       => true,
                'month_label'      => $monthLabel,
                'crm_teacher_name' => $crmTeacherName,
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
     * Fetch presence from local mirror, fall back to live API if mirror is empty.
     */
    protected function fetchPresenceFromCrm(
        Crm $crm,
        Group $group,
        Carbon $dateStart,
        Carbon $dateEnd,
    ): array {
        $crmClassId = $group->crm_class_id; // this is crm_classes.crm_id

        if (!$crmClassId) {
            throw new \RuntimeException('Groupe non lié à une classe CRM. Veuillez configurer crm_class_id.');
        }

        // crm_attendance.crm_class_id = crm_classes.id (local PK), not crm_classes.crm_id
        // We must look up the local id from the crm_classes table first
        $localClassId = \App\Models\CrmClass::where('crm_id', $crmClassId)->value('id');

        if (!$localClassId) {
            throw new \RuntimeException(
                "Classe CRM #{$crmClassId} non trouvée dans le miroir local. "
                . "Attendez le prochain sync automatique (toutes les 2h)."
            );
        }

        $mirrorAttendance = CrmAttendance::where('crm_class_id', $localClassId)
            ->whereBetween('date', [$dateStart->toDateString(), $dateEnd->toDateString()])
            ->get();

        if ($mirrorAttendance->isNotEmpty()) {
            return $this->parseFromMirror($mirrorAttendance, $dateStart, $dateEnd);
        }

        // Mirror has no data for this period — do NOT fall back to live API (causes 504 on shared hosting)
        // The sync runs every 2 hours and covers the last 2 months
        $from = $dateStart->toDateString();
        $to   = $dateEnd->toDateString();
        throw new \RuntimeException(
            "Aucune donnée de présence dans le miroir local pour la période {$from} → {$to}. "
            . "Le sync automatique couvre les 2 derniers mois. "
            . "Si vous avez besoin de données plus anciennes, contactez l'administrateur."
        );
    }

    protected function parseFromMirror(\Illuminate\Support\Collection $records, Carbon $dateStart, Carbon $dateEnd): array
    {
        $fullNames = CrmStudent::whereIn('crm_id', $records->pluck('crm_student_id')->unique())
            ->get(['crm_id', 'first_name', 'last_name'])
            ->mapWithKeys(fn($s) => [$s->crm_id => trim($s->first_name . ' ' . $s->last_name)]);

        $students = [];
        $dates = [];

        foreach ($records as $record) {
            $studentId = $record->crm_student_id;
            $studentName = $fullNames[$studentId] ?? "Étudiant #{$studentId}";
            $dateKey = $record->date instanceof \Carbon\Carbon
                ? $record->date->toDateString()
                : (string) $record->date;

            $dates[$dateKey] = true;

            if (!isset($students[$studentId])) {
                $students[$studentId] = [
                    'row_number'     => count($students) + 1,
                    'student_name'   => $studentName,
                    'crm_student_id' => $studentId,
                    'total_present'  => 0,
                    'total_absent'   => 0,
                    'auto_status'    => 'active',
                    'presence'       => [],
                    'raw_data'       => $record->raw_data ?? [],
                ];
            }

            $status = $record->is_present ? 'present' : 'absent';
            $students[$studentId]['presence'][] = ['date' => $dateKey, 'status' => $status, 'raw_value' => null];
            $status === 'present' ? $students[$studentId]['total_present']++ : $students[$studentId]['total_absent']++;
        }

        return $this->fillMissingDates($students, $dates, $dateStart, $dateEnd);
    }

    protected function parseFromLiveApi(Crm $crm, int $classId, Carbon $dateStart, Carbon $dateEnd): array
    {
        // $classId is LEVEL_SESSION_ID — API needs CLASS_ID
        $crmClass = \App\Models\CrmClass::where('crm_id', $classId)->first();
        $apiClassId = $crmClass?->class_id ?? $classId;

        $variants = [];
        $cursor = $dateStart->copy();
        while ($cursor->lte($dateEnd)) {
            $variants[] = ['date' => $cursor->toDateString()];
            $cursor->addDay();
        }

        $allRows = $crm->client()->parallelFetch(
            path: '/api/external/v1/session-presence',
            baseQuery: ['classId' => $apiClassId],
            variantQueries: $variants,
            pageSize: 25,
            concurrency: 3,
        );

        $students = [];
        $dates = [];

        foreach ($allRows as $row) {
            $studentId   = $row['STUDENT_ID'] ?? null;
            $firstName   = trim($row['FIRST_NAME'] ?? $row['STUDENT_NAME'] ?? '');
            $lastName    = trim($row['LAST_NAME'] ?? '');
            $studentName = $firstName || $lastName ? trim("{$firstName} {$lastName}") : "Étudiant #{$studentId}";
            $sessionDate = $row['SESSION_DATE'] ?? null;

            if (!$studentId || !$sessionDate) continue;

            try {
                $dateKey = Carbon::parse($sessionDate)->setTimezone('Africa/Casablanca')->toDateString();
            } catch (\Throwable) {
                continue;
            }

            $dates[$dateKey] = true;
            $status = $this->parsePresenceStatus($row);

            if (!isset($students[$studentId])) {
                $students[$studentId] = [
                    'row_number'     => count($students) + 1,
                    'student_name'   => $studentName,
                    'crm_student_id' => $studentId,
                    'total_present'  => 0,
                    'total_absent'   => 0,
                    'auto_status'    => 'active',
                    'presence'       => [],
                    'raw_data'       => $row,
                ];
            }

            $students[$studentId]['presence'][] = ['date' => $dateKey, 'status' => $status, 'raw_value' => json_encode($row)];
            $status === 'present' ? $students[$studentId]['total_present']++ : $students[$studentId]['total_absent']++;
        }

        return $this->fillMissingDates($students, $dates, $dateStart, $dateEnd);
    }

    protected function fillMissingDates(array $students, array $dates, Carbon $dateStart, Carbon $dateEnd): array
    {
        ksort($dates);
        $dateList = array_keys($dates);

        foreach ($students as &$student) {
            $byDate = collect($student['presence'])->keyBy('date');
            $complete = [];
            foreach ($dateList as $date) {
                if ($byDate->has($date)) {
                    $complete[] = $byDate->get($date);
                } else {
                    $complete[] = ['date' => $date, 'status' => 'absent', 'raw_value' => null];
                    $student['total_absent']++;
                }
            }
            $student['presence'] = $complete;
        }

        return [
            'students'     => array_values($students),
            'date_start'   => $dateStart->toDateString(),
            'date_end'     => $dateEnd->toDateString(),
            'total_days'   => count($dateList),
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
