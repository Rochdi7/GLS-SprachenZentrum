<?php

namespace App\Services\Crm\Stats;

use App\Services\Crm\Crm;

/**
 * Builds the per-class "Statistique de groupe" payment matrix.
 *
 * Pure aggregation: takes the class's pre-loaded student list + service list
 * (sent from the browser to skip extra API calls), fans out a single paged
 * scan of /payment-allocations filtered by classId, and produces a
 * student × service grid with paid/partial/unpaid/na cells.
 *
 * Lives outside CrmController so the controller stays a thin wrapper and
 * this logic can be unit-tested or reused (e.g. by a CLI snapshot command).
 */
class PaymentMatrixBuilder
{
    /**
     * Build the matrix.
     *
     * @param  array<int, array<string,mixed>>  $rawStudents  Browser-supplied: STUDENT_ID, REGISTRATION_ID, name parts, _bucket
     * @param  array<int, array<string,mixed>>  $rawServiceList  Browser-supplied: SERVICE_TYPE_NAME, PRICE, DUE_DATE
     * @return array{success: true, class: array, services: array, students: array, totals: array, meta: array}
     */
    public function build(
        Crm $crm,
        int $classId,
        array $rawStudents,
        array $rawServiceList,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        array $classMeta = [],
    ): array {
        // 1) Normalize the student list.
        $students = $this->normalizeStudents($rawStudents);
        if (empty($students)) {
            return $this->emptyResponse($classId, $classMeta);
        }

        // 2) Fetch payment allocations for each student in parallel.
        //    The API doesn't support classId, so we use studentId instead.
        $baseQuery = array_filter([
            'strStoreId'   => $strStoreId,
            'schoolYearId' => $schoolYearId,
        ], fn($v) => $v !== null);

        // Create variants for each student
        $variantQueries = array_map(fn($student) => ['studentId' => $student['student_id']], $students);

        $allocRows = [];
        try {
            // Fetch all payment allocations in parallel with reduced concurrency to avoid rate limiting
            $allocRows = $crm->client()->parallelFetch(
                path: '/api/external/v1/payment-allocations',
                baseQuery: $baseQuery,
                variantQueries: $variantQueries,
                pageSize: 25,
                concurrency: 2,
                interBatchDelayMs: 500,
            );
        } catch (\Throwable $e) {
            // Log the error and continue with empty allocations
            \Illuminate\Support\Facades\Log::warning("Failed to fetch payment allocations: " . $e->getMessage(), [
                'classId' => $classId,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // 3) Build the matrix.
        [$matrix, $services, $serviceDate, $canonicalPrice] = $this->seedFromServiceList($rawServiceList, $students);
        $allocSum = $this->sumAllocations($allocRows, $services, $serviceDate);
        $this->mergeAllocations($matrix, $allocSum, $students, $canonicalPrice);

        // 4) Order columns chronologically by DUE_DATE.
        $serviceLabels = $this->orderServices(array_keys($services), $serviceDate, $services);

        // 5) Build the per-student cell rows.
        $rows = $this->buildStudentRows($students, $serviceLabels, $matrix);

        // 6) Column totals.
        $totals = $this->columnTotals($rows, $serviceLabels);

        return [
            'success'  => true,
            'class'    => [
                'id'        => $classId,
                'name'      => $classMeta['name']      ?? null,
                'reference' => $classMeta['reference'] ?? null,
                'teacher'   => $classMeta['teacher']   ?? null,
            ],
            'services' => $serviceLabels,
            'students' => $rows,
            'totals'   => $totals,
            'meta'     => [
                'student_count'   => count($students),
                'service_count'   => count($serviceLabels),
                'sub_row_count'   => 0,
                'alloc_row_count' => count($allocRows),
            ],
        ];
    }

    /**
     * Coerce the browser-supplied students payload into a clean internal shape.
     * Each student is tagged with its bucket (active/archived/canceled) so the
     * UI can paint the N°/name band accordingly.
     *
     * @return array<int, array{student_id:int, registration_id:?int, reference:?string, name:string, bucket:string}>
     */
    protected function normalizeStudents(array $rawStudents): array
    {
        $out = [];
        foreach ($rawStudents as $s) {
            if (!is_array($s) || empty($s['STUDENT_ID'])) continue;

            $bucket = (string) ($s['_bucket'] ?? 'active');
            if (!in_array($bucket, ['active', 'archived', 'canceled'], true)) {
                $bucket = 'active';
            }

            $out[] = [
                'student_id'      => (int) $s['STUDENT_ID'],
                'registration_id' => isset($s['REGISTRATION_ID']) ? (int) $s['REGISTRATION_ID'] : null,
                'reference'       => $s['STUDENT_REFERENCE'] ?? null,
                'name'            => trim(($s['STUDENT_FIRST_NAME'] ?? '') . ' ' . ($s['STUDENT_LAST_NAME'] ?? '')) ?: ('#' . $s['STUDENT_ID']),
                'bucket'          => $bucket,
            ];
        }
        return $out;
    }

    /**
     * Pre-seed the matrix from the class's SERVICE_LIST (sent by the browser).
     *
     * For every active student we create a cell per declared service with
     * paid=0, total=service price. This is what produces the red "unpaid"
     * cells for active students who haven't paid yet, without an extra
     * /subscription-services call.
     *
     * Archived/canceled students are intentionally NOT seeded — their dues
     * no longer apply; only their historical /payment-allocations matter.
     *
     * @return array{0: array, 1: array<string,int>, 2: array<string,string>, 3: array<string,float>}
     *         [matrix, services index, serviceDate, canonicalPrice]
     */
    protected function seedFromServiceList(array $classServiceList, array $students): array
    {
        $matrix         = [];
        $services       = [];
        $serviceDate    = [];
        $canonicalPrice = [];

        $activeIds = array_flip(
            array_map(
                fn($s) => $s['student_id'],
                array_filter($students, fn($s) => $s['bucket'] === 'active'),
            ),
        );

        foreach ($classServiceList as $svc) {
            if (!is_array($svc)) continue;
            $label = trim((string) ($svc['SERVICE_TYPE_NAME'] ?? ''));
            if ($label === '') continue;
            if (!isset($services[$label])) {
                $services[$label] = count($services);
            }
            $due = $svc['DUE_DATE'] ?? null;
            if ($due) {
                $d = substr((string) $due, 0, 10);
                if (!isset($serviceDate[$label]) || strcmp($d, $serviceDate[$label]) < 0) {
                    $serviceDate[$label] = $d;
                }
            }
            $price = (float) ($svc['PRICE'] ?? $svc['TOTAL_PRICE'] ?? 0);
            if ($price <= 0) continue;

            // Highest price seen for the label is the canonical "full" price.
            // Lets us correctly classify partial payments by archived/canceled
            // students whose subscription totals are no longer available.
            if (!isset($canonicalPrice[$label]) || $price > $canonicalPrice[$label]) {
                $canonicalPrice[$label] = $price;
            }
            foreach ($activeIds as $sid => $_) {
                $matrix[$sid][$label] = ['paid' => 0.0, 'total' => $price];
            }
        }

        return [$matrix, $services, $serviceDate, $canonicalPrice];
    }

    /**
     * Sum allocation amounts per (student, service) and accumulate the
     * column set + earliest date metadata.
     *
     * @return array<int, array<string, float>>  [studentId][label] = paid total
     */
    protected function sumAllocations(array $allocRows, array &$services, array &$serviceDate): array
    {
        $allocSum = [];
        foreach ($allocRows as $alloc) {
            $sid    = (int) ($alloc['STUDENT_ID'] ?? 0);
            $label  = trim((string) ($alloc['SERVICE_TYPE_NAME'] ?? ''));
            $amount = (float) ($alloc['AMOUNT'] ?? 0);
            if (!$sid || $label === '' || $amount <= 0) continue;
            $allocSum[$sid][$label] = ($allocSum[$sid][$label] ?? 0) + $amount;

            if (!isset($services[$label])) {
                $services[$label] = count($services);
            }
            $allocDate = $alloc['EFFECTIVE_DATE_PAYMENT_ALLOCATION']
                ?? $alloc['EFFECTIVE_DATE_PAYMENT']
                ?? null;
            if ($allocDate) {
                $d = substr((string) $allocDate, 0, 10);
                if (!isset($serviceDate[$label]) || strcmp($d, $serviceDate[$label]) < 0) {
                    $serviceDate[$label] = $d;
                }
            }
        }
        return $allocSum;
    }

    /**
     * Merge the allocation sums into the matrix. Filters to students actually
     * in the class. For non-seeded cells (archived/canceled) we use the
     * canonical class price as the total so partial payments are detected
     * (200/300 → orange, not green).
     */
    protected function mergeAllocations(array &$matrix, array $allocSum, array $students, array $canonicalPrice): void
    {
        $allowedIds = array_flip(array_map(fn($s) => $s['student_id'], $students));

        foreach ($allocSum as $sid => $byLabel) {
            if (!isset($allowedIds[$sid])) continue;
            foreach ($byLabel as $label => $amount) {
                $existing = $matrix[$sid][$label] ?? null;
                $expected = $canonicalPrice[$label] ?? null;

                if ($existing === null) {
                    $total = ($expected !== null && $expected > 0) ? $expected : $amount;
                    $matrix[$sid][$label] = [
                        'paid'  => min($amount, $total),
                        'total' => $total,
                    ];
                } else {
                    $maxPaid = max($existing['paid'], $amount);
                    if ($existing['total'] > 0) {
                        $maxPaid = min($maxPaid, $existing['total']);
                    } else {
                        $existing['total'] = $expected ?: $amount;
                        $maxPaid = min($maxPaid, $existing['total']);
                    }
                    $existing['paid'] = $maxPaid;
                    $matrix[$sid][$label] = $existing;
                }
            }
        }
    }

    /**
     * Order columns chronologically by DUE_DATE. Inscription fees win
     * tie-breakers so they anchor each phase (Sept inscription first,
     * mid-year B2 inscription between Février and Mars, etc).
     *
     * @return array<int, string>
     */
    protected function orderServices(array $labels, array $serviceDate, array $servicesIndex): array
    {
        $isInscription = fn(string $label): bool => stripos($label, 'inscription') !== false;

        usort($labels, function (string $a, string $b) use ($serviceDate, $isInscription, $servicesIndex) {
            $da = $serviceDate[$a] ?? '9999-12-31';
            $db = $serviceDate[$b] ?? '9999-12-31';
            $cmp = strcmp($da, $db);
            if ($cmp !== 0) return $cmp;
            $ia = $isInscription($a) ? 0 : 1;
            $ib = $isInscription($b) ? 0 : 1;
            if ($ia !== $ib) return $ia <=> $ib;
            return $servicesIndex[$a] <=> $servicesIndex[$b];
        });

        return $labels;
    }

    /**
     * For each student, build the cell-by-service row with paid/partial/
     * unpaid/na status derived from total vs paid.
     */
    protected function buildStudentRows(array $students, array $serviceLabels, array $matrix): array
    {
        $rows = [];
        foreach ($students as $s) {
            $cells = [];
            foreach ($serviceLabels as $label) {
                $cell = $matrix[$s['student_id']][$label] ?? null;
                if (!$cell) {
                    $cells[$label] = ['status' => 'na', 'paid' => 0, 'total' => 0];
                    continue;
                }
                $status = match (true) {
                    $cell['total'] <= 0                    => 'na',
                    $cell['paid'] >= $cell['total'] - 0.01 => 'paid',
                    $cell['paid'] <= 0.01                  => 'unpaid',
                    default                                => 'partial',
                };
                $cells[$label] = [
                    'status' => $status,
                    'paid'   => round($cell['paid'], 2),
                    'total'  => round($cell['total'], 2),
                ];
            }
            $rows[] = $s + ['cells' => $cells];
        }
        return $rows;
    }

    /**
     * Sum of paid amounts per column (paid + partial cells only).
     */
    protected function columnTotals(array $rows, array $serviceLabels): array
    {
        $totals = [];
        foreach ($serviceLabels as $label) {
            $sum = 0.0;
            foreach ($rows as $row) {
                $status = $row['cells'][$label]['status'] ?? null;
                if ($status === 'paid' || $status === 'partial') {
                    $sum += (float) $row['cells'][$label]['paid'];
                }
            }
            $totals[$label] = round($sum, 2);
        }
        return $totals;
    }

    protected function emptyResponse(int $classId, array $classMeta): array
    {
        return [
            'success'  => true,
            'class'    => [
                'id'        => $classId,
                'name'      => $classMeta['name']      ?? null,
                'reference' => $classMeta['reference'] ?? null,
                'teacher'   => $classMeta['teacher']   ?? null,
            ],
            'services' => [],
            'students' => [],
            'totals'   => [],
            'meta'     => [
                'student_count'   => 0,
                'service_count'   => 0,
                'sub_row_count'   => 0,
                'alloc_row_count' => 0,
            ],
        ];
    }
}
