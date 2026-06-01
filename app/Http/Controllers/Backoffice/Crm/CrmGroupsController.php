<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\CrmException;
use App\Services\Crm\Stats\PaymentMatrixBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class- and group-level CRM pages: classes browser, level sessions browser,
 * subscription services list, employee salary classes list, and the
 * "Statistique de groupe" payment matrix (JSON endpoint + Excel export).
 *
 * Heavy aggregation lives in {@see PaymentMatrixBuilder}.
 */
class CrmGroupsController extends BaseCrmController
{
    public function groupsClasses(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();

        // Get class statuses to find "En formation"
        $lovClassStatuses = $this->lovs->classStatuses($strStoreId);
        
        // Find status ID for "En formation"
        $defaultStatusId = null;
        foreach ($lovClassStatuses as $status) {
            if (trim($status['name']) === 'En formation') {
                $defaultStatusId = $status['id'];
                break;
            }
        }

        // Use default status if statusId is not set in request
        $statusId = $r->filled('statusId') ? (int)$r->query('statusId') : $defaultStatusId;

        // "Fast First 5" strategy: if on page 0 and no size is specified, 
        // default to 5 for an instant first-paint, then preload.
        $size = (int) $r->query('size', 5);

        return $this->render(
            'backoffice.crm.classes',
            fn (?int $sid) => tap($this->scopedCrm()->groups()->classes(
                page: (int) $r->query('page', 0),
                size: $size,
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                schoolDepartmentId: $r->filled('schoolDepartmentId') ? (int) $r->query('schoolDepartmentId') : null,
                schoolStageId: $r->filled('schoolStageId') ? (int) $r->query('schoolStageId') : null,
                schoolLevelId: $r->filled('schoolLevelId') ? (int) $r->query('schoolLevelId') : null,
                employeeTeacherId: $r->filled('employeeTeacherId') ? (int) $r->query('employeeTeacherId') : null,
                statusId: $statusId,
                history: $r->query('history') ?: null,
            ), function () use ($sid, $r, $statusId) {
                // Background preload: fetch the STANDARD size (20) and next 3 pages
                $preloadQuery = array_filter([
                    'strStoreId'         => $sid,
                    'schoolYearId'       => $r->query('schoolYearId'),
                    'schoolDepartmentId' => $r->query('schoolDepartmentId'),
                    'schoolStageId'      => $r->query('schoolStageId'),
                    'schoolLevelId'      => $r->query('schoolLevelId'),
                    'employeeTeacherId'  => $r->query('employeeTeacherId'),
                    'statusId'           => $statusId,
                    'history'            => $r->query('history'),
                    'size'               => 20, // Always preload at standard size
                ], fn($v) => $v !== null);

                $this->scopedCrm()->client()->preload('/api/external/v1/groups/classes', $preloadQuery, 4, 0);
            }),
            extra: [
                'lovSchoolLevels'      => $this->lovs->schoolLevels($strStoreId),
                'lovTeachers'          => $this->lovs->teachers($strStoreId),
                'lovClassStatuses'     => $lovClassStatuses,
                'lovSchoolYears'       => $this->lovs->schoolYears($strStoreId),
                'lovSchoolDepartments' => $this->lovs->schoolDepartments($strStoreId),
                'lovSchoolStages'      => $this->lovs->schoolStages($strStoreId),
                'defaultStatusId'      => $defaultStatusId, // Pass to view
            ],
        );
    }

    public function groupsLevelSessions(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();

        return $this->render(
            'backoffice.crm.level-sessions',
            fn (?int $sid) => $this->scopedCrm()->groups()->levelSessions(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            ),
            extra: [
                'lovSchoolYears' => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function subscriptionServices(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();

        return $this->render(
            'backoffice.crm.subscription-services',
            fn (?int $sid) => $this->scopedCrm()->subscriptionServices()->list(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                id: $r->filled('id') ? (int) $r->query('id') : null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                registrationId: $r->filled('registrationId') ? (int) $r->query('registrationId') : null,
                dueDate: $r->query('dueDate') ?: null,
                levelSessionPackageId: $r->filled('levelSessionPackageId') ? (int) $r->query('levelSessionPackageId') : null,
                subscriptionServiceStatusId: $r->filled('subscriptionServiceStatusId') ? (int) $r->query('subscriptionServiceStatusId') : null,
                levelSessionId: $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
            ),
            extra: [
                'lovLevelSessions' => $this->lovs->levelSessions($strStoreId),
                'lovPackages'      => $this->lovs->levelSessionPackages($strStoreId),
                'lovSchoolYears'   => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function employeeSalaries(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();

        // Default the period to current month/year so the page isn't blank
        // on first load — the API has no implicit "this month" filter.
        $operationMonth = $r->filled('operationMonth') ? (int) $r->query('operationMonth') : (int) now()->month;
        $operationYear  = $r->filled('operationYear')  ? (int) $r->query('operationYear')  : (int) now()->year;

        return $this->render(
            'backoffice.crm.employee-salaries',
            fn (?int $sid) => $this->scopedCrm()->employeeSalaries()->calculatedSalaryClasses(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                employeeId: $r->filled('employeeId') ? (int) $r->query('employeeId') : null,
                classId: $r->filled('classId') ? (int) $r->query('classId') : null,
                operationMonth: $operationMonth,
                operationYear: $operationYear,
            ),
            extra: [
                'lovClasses'     => $this->lovs->classes($strStoreId),
                'operationMonth' => $operationMonth,
                'operationYear'  => $operationYear,
            ],
        );
    }

    /**
     * JSON: per-class "Statistique de groupe" payment matrix.
     *
     * Aggregation is delegated to {@see PaymentMatrixBuilder}. Students and
     * the class's SERVICE_LIST are posted from the browser to avoid an extra
     * /groups/classes re-scan that would also fail when a UI filter hides
     * the row from page 0.
     */
    public function classPaymentMatrix(Request $r, int $classId, PaymentMatrixBuilder $builder): JsonResponse
    {
        try {
            $data = $builder->build(
                crm: $this->scopedCrm(),
                classId: $classId,
                rawStudents: $this->decodeJsonInput($r->input('students', [])),
                rawServiceList: $this->decodeJsonInput($r->input('serviceList', [])),
                strStoreId: $this->currentStrStoreId(),
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                classMeta: [
                    'name'      => $r->input('className'),
                    'reference' => $r->input('classRef'),
                    'teacher'   => $r->input('classTeacher'),
                ],
            );

            return response()->json($data);
        } catch (CrmException $e) {
            $friendly = $e->status === 429
                ? 'Trop de requêtes vers le CRM. Patientez ~1 minute.'
                : $e->getMessage();

            return response()->json([
                'success' => false,
                'message' => $friendly,
                'status'  => $e->status,
            ], $e->status === 429 ? 429 : 502);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Matrix Aggregation Error: " . $e->getMessage(), [
                'classId' => $classId,
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la génération de la statistique : " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Excel (.xlsx) export of the per-class payment matrix.
     *
     * Re-uses {@see classPaymentMatrix} so the on-screen modal and the
     * spreadsheet can never diverge in layout or color logic. The xlsx
     * formatting (colors, borders, frozen panes) lives here.
     */
    public function classPaymentMatrixExport(Request $r, int $classId, PaymentMatrixBuilder $builder): StreamedResponse
    {
        $json = $this->classPaymentMatrix($r, $classId, $builder);
        $data = $json->getData(true);

        $services  = $data['services'] ?? [];
        $students  = $data['students'] ?? [];
        $totals    = $data['totals']   ?? [];
        $className = $data['class']['name'] ?? ('classe-' . $classId);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Statistique');

        // Color palette (RGB without #) — keep in sync with the modal CSS.
        $colors = [
            'paid'         => ['fill' => 'B3E6C2', 'font' => '14532D'],
            'partial'      => ['fill' => 'F5C98A', 'font' => '7A3E00'],
            'unpaid'       => ['fill' => 'F1A8A0', 'font' => '7F1D1D'],
            'na'           => ['fill' => 'D9D9D9', 'font' => '495057'],
            'header'       => ['fill' => 'F8F9FA', 'font' => '6C757D'],
            'band_unpaid'  => ['fill' => 'F1A8A0', 'font' => '7F1D1D'],
            'band_archive' => ['fill' => 'D9D9D9', 'font' => '495057'],
            'total'        => ['fill' => 'FFFFFF', 'font' => '000000'],
        ];

        $applyFill = function (string $cell, string $bg, string $fg) use ($sheet) {
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bg);
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB($fg);
        };

        // 1) Header row
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'Étudiant');
        foreach ($services as $i => $label) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
            $sheet->setCellValue("{$col}1", $label);
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($services) + 2);
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $applyFill($headerRange, $colors['header']['fill'], $colors['header']['font']);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 2) Student rows
        $row = 2;
        foreach ($students as $idx => $s) {
            $bucket = $s['bucket'] ?? 'active';
            $hasPaid = false; $hasUnpaid = false;
            foreach ($services as $label) {
                $st = $s['cells'][$label]['status'] ?? null;
                if ($st === 'paid' || $st === 'partial') $hasPaid = true;
                elseif ($st === 'unpaid')                $hasUnpaid = true;
            }
            $band = match (true) {
                $bucket === 'canceled'           => 'band_unpaid',
                $bucket === 'archived'           => 'band_archive',
                !$hasPaid && $hasUnpaid          => 'band_unpaid',
                default                          => null,
            };

            $sheet->setCellValue("A{$row}", '#' . $idx);
            $sheet->setCellValueExplicit("B{$row}", $s['name'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            if ($band !== null) {
                $applyFill("A{$row}:B{$row}", $colors[$band]['fill'], $colors[$band]['font']);
                $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            }
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            foreach ($services as $i => $label) {
                $col  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
                $cell = $s['cells'][$label] ?? ['status' => 'na'];
                $status = $cell['status'];
                $value = match ($status) {
                    'paid'    => number_format((float) $cell['total'], 2, '.', '') . ' DH',
                    'partial' => number_format((float) $cell['paid'],  2, '.', '') . ' DH',
                    'unpaid'  => '0.00 DH',
                    default   => '',
                };
                $sheet->setCellValueExplicit("{$col}{$row}", $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $c = $colors[$status] ?? $colors['na'];
                $applyFill("{$col}{$row}", $c['fill'], $c['font']);
            }

            $rowRange = "A{$row}:{$lastCol}{$row}";
            $sheet->getStyle($rowRange)->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $startNumCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3);
            $sheet->getStyle("{$startNumCol}{$row}:{$lastCol}{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowRange)->getFont()->setSize(10)->setBold(true);
            $sheet->getRowDimension($row)->setRowHeight(20);

            $row++;
        }

        // 3) Totals row
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Total');
        $sheet->getStyle("A{$row}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        foreach ($services as $i => $label) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
            $v   = (float) ($totals[$label] ?? 0);
            $sheet->setCellValueExplicit("{$col}{$row}", number_format($v, 2, '.', '') . ' DH', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $totalRange = "A{$row}:{$lastCol}{$row}";
        $sheet->getStyle($totalRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($totalRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle($totalRange)->getBorders()->getTop()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheet->getRowDimension($row)->setRowHeight(22);

        // 4) Borders + column widths + frozen header
        $fullRange = "A1:{$lastCol}{$row}";
        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('FFFFFF');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(28);
        for ($i = 0; $i < count($services); $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
            $sheet->getColumnDimension($col)->setWidth(18);
        }
        $sheet->freezePane('C2');

        // 5) Stream as .xlsx
        $filename = 'statistique-groupe-' . preg_replace('/[^A-Za-z0-9]+/', '-', strtolower($className)) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
        ]);
    }

    /**
     * Accept either a raw array (Laravel parsed it from JSON body) or a
     * JSON string (sent via classic form-encoded export form).
     */
    protected function decodeJsonInput(mixed $value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }
        return is_array($value) ? $value : [];
    }
}
