<?php

namespace App\Services\Payroll;

use App\Models\Group;
use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Models\PresenceRecord;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the full attendance Excel import flow.
 *
 * Responsibilities:
 * - Store uploaded file
 * - Create versioned import snapshot
 * - Persist parsed students + daily presence records
 * - Trigger payment calculation
 */
class PresenceImportService
{
    public function __construct(
        protected PresenceExcelParserService $parser,
        protected ProfPaymentCalculationService $calculator,
    ) {}

    /**
     * Import an attendance Excel file for a group.
     */
    public function import(
        Group $group,
        UploadedFile $file,
        Carbon $month,
        ?float $paymentPerStudent = null,
        ?string $notes = null,
        ?int $importedBy = null,
        ?Carbon $dateStart = null,
        ?Carbon $dateEnd = null,
    ): PresenceImport {
        return DB::transaction(function () use ($group, $file, $month, $paymentPerStudent, $notes, $importedBy, $dateStart, $dateEnd) {

            // 1. Store the uploaded file
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('payroll/presence', 'local');

            // 2. Determine next version number for this group
            $nextVersion = ($group->presenceImports()->max('version') ?? 0) + 1;

            // 3. Parse the Excel file (period overrides come from the form)
            $parsed = $this->parser->parse($file, $month, $dateStart, $dateEnd);

            if (empty($parsed['students'])) {
                $debug = $parsed['debug'] ?? 'N/A';
                $dateCols = count($parsed['date_columns'] ?? []);
                throw new \RuntimeException(
                    'Aucun étudiant trouvé dans le fichier. '
                    ."Colonnes de dates détectées: {$dateCols}. "
                    ."Diagnostic: {$debug}"
                );
            }

            // 4. Create the import record
            $import = PresenceImport::create([
                'group_id' => $group->id,
                'version' => $nextVersion,
                'month' => $month->copy()->startOfMonth(),
                'date_start' => $parsed['date_start'],
                'date_end' => $parsed['date_end'],
                'total_days' => $parsed['total_days'],
                'payment_per_student' => $paymentPerStudent,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'notes' => $notes,
                'imported_by' => $importedBy,
            ]);

            // 5. Persist each student and their daily presence records
            foreach ($parsed['students'] as $studentData) {
                $student = PresenceImportStudent::create([
                    'presence_import_id' => $import->id,
                    'row_number' => $studentData['row_number'],
                    'student_name' => $studentData['student_name'],
                    'total_present' => $studentData['total_present'],
                    'total_absent' => $studentData['total_absent'],
                    'status' => $studentData['auto_status'] ?? 'active',
                    'row_color' => $studentData['row_color'] ?? null,
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

            // 6. Calculate payment (categories + summary)
            $this->calculator->calculate($import);

            return $import;
        });
    }

    /**
     * Manually override (or clear) one of the student's 4 weekly amounts,
     * then recompute the import totals.
     */
    public function overrideStudentWeek(PresenceImportStudent $student, int $week, ?float $amount): void
    {
        DB::transaction(function () use ($student, $week, $amount) {
            $this->calculator->overrideStudentWeek($student, $week, $amount);
        });
    }

    /**
     * Recalculate payment for an existing import (e.g., after rate change).
     */
    public function recalculate(PresenceImport $import): void
    {
        $this->calculator->calculate($import);
    }
}
