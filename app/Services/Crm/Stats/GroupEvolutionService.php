<?php

namespace App\Services\Crm\Stats;

use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Per-group evolution analyzer.
 *
 * For a given center + date range, classifies each (student, group) pair
 * into one of five buckets:
 *
 *   - Début (kick-off): student whose **first payment month** for this group
 *     equals the group's START_DATE month. They were there on day one.
 *     Sum of Début ≈ number of students the group launched with.
 *
 *   - Ajout (late arrival): student whose **first payment month** for this
 *     group is STRICTLY AFTER the group's START_DATE month. Captures students
 *     who joined an already-running group (e.g. group started in Sep 2025 and
 *     the student's first payment is for Oct 2025 — that's an Ajout).
 *     A service literally tagged "inscription" is also counted, as a fallback
 *     for the rare cases where dates don't carry the right signal.
 *
 *   - Changement de groupe: student stopped paying group G AND started paying
 *     a DIFFERENT group within 30 days (typically same school year). Detected
 *     by comparing every student's payment-allocation classes within the
 *     range — appearing in 2+ classes with consecutive payment months ⇒
 *     they moved. Works across long ranges (a1→b2) because the gap check
 *     looks at consecutive class periods, not the full span.
 *
 *   - Quittant (départ définitif): student had a paid month in range, then
 *     **missed the next month** (1 missed month rule), and has NO other
 *     active payment elsewhere in the school.
 *
 *   - Actifs actuellement: current CLASS_COUNT_STUDENTS_ACTIVE from
 *     /groups/classes — no inference needed, the API already counts these.
 *
 * Why payment-allocations (not /payments or /registrations)?
 *   - /payments has no classId filter — we can't tell which group a payment
 *     went to.
 *   - /registrations doesn't expose payment dates, only status timestamps
 *     which are sometimes edited months after the fact.
 *   - /payment-allocations exposes STUDENT_ID + CLASS_ID + SERVICE_TYPE_NAME
 *     + EFFECTIVE_DATE_PAYMENT — exactly what we need.
 */
class GroupEvolutionService
{
    public const CACHE_TTL = 3600; // 60 minutes

    /** Window (days) inside which a stop-then-start counts as a transfer. */
    public const CHANGEMENT_WINDOW_DAYS = 30;

    public function __construct(protected Crm $crm) {}

    /**
     * Build the full evolution report for a center + date range.
     *
     * @return array{
     *   groups: array<int, array{class_id:int, name:string, debuts:int, ajouts:int, quittants:int, changements:int, actifs:int}>,
     *   totals: array{debuts:int, ajouts:int, quittants:int, changements:int, actifs:int, groups:int},
     *   range: array{start:string, end:string},
     * }
     */
    /**
     * Build the full evolution report for a center + date range
     */
    public function build(Crm $crm, ?int $strStoreId, string $startDate, string $endDate, bool $bustCache = false, ?array $classIds = null, bool $includeAllGroups = true): array
    {
        // First get ALL groups with metadata for filter, to get all groups' start/end dates
        $allGroups = [];
        if ($includeAllGroups) {
            $allClasses = $this->fetchClasses($crm, $strStoreId, $bustCache);
            $allGroups = array_map(function ($c) {
                return [
                    'class_id' => (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0),
                    'name' => (string) ($c['NAME'] ?? $c['REFERENCE'] ?? ''),
                    'start_date' => $c['START_DATE'] ?? null,
                    'end_date' => $c['END_DATE'] ?? null,
                    'actifs' => (int) ($c['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0),
                ];
            }, $allClasses);
        }

        $cacheKey = "crm.group_evolution.{$strStoreId}.{$startDate}.{$endDate}";
        if ($classIds) {
            sort($classIds);
            $cacheKey .= '.' . implode('_', $classIds);
        }
        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($crm, $strStoreId, $startDate, $endDate, $bustCache, $classIds) {
            return $this->compute($crm, $strStoreId, $startDate, $endDate, $bustCache, $classIds);
        });

        // Attach all groups to the result so filter dropdown
        $result['allGroups'] = $allGroups;
        return $result;
    }

    protected function compute(Crm $crm, ?int $strStoreId, string $startDate, string $endDate, bool $bustCache = false, ?array $classIds = null): array
    {
        $this->lastFetchError = null;

        try {
            $allClasses = $this->fetchClasses($crm, $strStoreId, $bustCache);

            \Illuminate\Support\Facades\Log::info('GroupEvolution: compute() initial', [
                'numAllClasses' => count($allClasses),
                'classIds' => $classIds,
            ]);

            // Get classes to display
            $displayClasses = $allClasses;
            if ($classIds) {
                $allow = array_flip($classIds);
                $displayClasses = array_filter($displayClasses, fn($c) => isset($allow[(int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0)]));
                \Illuminate\Support\Facades\Log::info('GroupEvolution: filtered classes', [
                    'numDisplayClasses' => count($displayClasses),
                    'classIds' => $classIds,
                    'allClassIds' => array_map(fn($c) => (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0), $allClasses),
                ]);
            }

            if (empty($displayClasses)) {
                return $this->emptyResult($startDate, $endDate);
            }

            $classStartMonth = [];
            $classEndDate    = [];
            $classActiveStudents = [];
            $classArchivedStudents = [];
            $classCanceledStudents = [];
            $classAllStudents = [];
            $studentNames = []; // studentId => studentName

            // Populate data from ALL classes (needed for changements detection)
            foreach ($allClasses as $c) {
                $cid     = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
                if (!$cid) continue;

                $start   = $c['START_DATE'] ?? null;
                $end     = $c['END_DATE'] ?? null;

                // Process class start date, skip future dates
                if ($cid && $start) {
                    try {
                        $parsedStart = Carbon::parse($start);
                        if (!$parsedStart->isFuture()) {
                            $classStartMonth[$cid] = $parsedStart->format('Y-m');
                        }
                    } catch (\Throwable) {
                    }
                }

                if ($cid && $end) {
                    try {
                        $classEndDate[$cid] = Carbon::parse($end)->toDateString();
                    } catch (\Throwable) {
                    }
                }

                // Parse student lists and collect names
                $classActiveStudents[$cid] = $this->parseStudentList($c['LIST_STUDENT_ACTIVE'] ?? null, $studentNames);
                $classArchivedStudents[$cid] = $this->parseStudentList($c['LIST_STUDENT_ARCHIVED'] ?? null, $studentNames);
                $classCanceledStudents[$cid] = $this->parseStudentList($c['LIST_STUDENT_CANCELED'] ?? null, $studentNames);

                // Combine all students for this class
                $classAllStudents[$cid] = array_unique(array_merge(
                    $classActiveStudents[$cid],
                    $classArchivedStudents[$cid],
                    $classCanceledStudents[$cid]
                ));
            }

            // Collect all student IDs from the DISPLAY classes
            $allStudentIds = [];
            foreach ($displayClasses as $c) {
                $cid = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
                if (isset($classAllStudents[$cid])) {
                    $allStudentIds = array_merge($allStudentIds, $classAllStudents[$cid]);
                }
            }
            $allStudentIds = array_unique($allStudentIds);

            // Fetch allocations only for display classes and students (no date limit!)
            $allocations = $this->fetchAllocations($crm, $strStoreId, $classIds, $allStudentIds);

            // Step 1: Build (student, class) map and first payment month
            $studentClasses = []; // studentId => classId => bool
            $studentFirstPaymentMonth = []; // studentId => classId => Y-m (only non-inscription)
            $studentInscriptionMonth = []; // studentId => classId => Y-m (only inscription)
            $inscriptionServices = []; // studentId => classId => bool
            $classInferredStartMonth = []; // classId => Y-m (from earliest payment, including inscription)
            $studentAllocationsByMonth = []; // studentId => classId => ym => count

            foreach ($allocations as $alloc) {
                $sid = (int) ($alloc['STUDENT_ID'] ?? 0);
                $cid = (int) ($alloc['CLASS_ID'] ?? 0);
                if (!$sid || !$cid) continue;

                $studentClasses[$sid][$cid] = true;

                // Track first payment month for this (student, class) - SKIP INSCRIPTION
                $date = $alloc['EFFECTIVE_DATE_PAYMENT_ALLOCATION']
                    ?? $alloc['EFFECTIVE_DATE_PAYMENT']
                    ?? null;
                $serviceType = $alloc['SERVICE_TYPE_NAME'] ?? '';
                $isInscription = $this->isInscription($serviceType);

                // Track inscription services
                if ($isInscription) {
                    $inscriptionServices[$sid][$cid] = true;
                }

                if ($date) {
                    try {
                        $ym = Carbon::parse($date)->format('Y-m');
                        $studentAllocationsByMonth[$sid][$cid][$ym][] = $alloc;

                        // Update class inferred start month (even if it's inscription)
                        if (!isset($classInferredStartMonth[$cid]) || $ym < $classInferredStartMonth[$cid]) {
                            $classInferredStartMonth[$cid] = $ym;
                        }

                        if ($isInscription) {
                            // Track inscription month
                            if (!isset($studentInscriptionMonth[$sid][$cid]) || $ym < $studentInscriptionMonth[$sid][$cid]) {
                                $studentInscriptionMonth[$sid][$cid] = $ym;
                            }
                        } else {
                            // Only update student first payment month if it's NOT inscription
                            if (!isset($studentFirstPaymentMonth[$sid][$cid]) || $ym < $studentFirstPaymentMonth[$sid][$cid]) {
                                $studentFirstPaymentMonth[$sid][$cid] = $ym;
                            }
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to parse allocation date", ['error' => $e->getMessage(), 'date' => $date]);
                    }
                }
            }

            // Log debug info for specific students
            $targetStudents = ['MOHAMED ADAM EL MANSOUM', 'ZOUHAIR EL MITI', 'ADAM EL GOUT', 'MAROUA EL HAMDANI'];
            \Illuminate\Support\Facades\Log::info("GroupEvolution: Target students and allocations", [
                'classIds' => $classIds,
                'targetStudents' => $targetStudents,
                'studentNames' => $studentNames,
                'studentAllocationsByMonth' => $studentAllocationsByMonth,
                'studentFirstPaymentMonth' => $studentFirstPaymentMonth,
                'studentInscriptionMonth' => $studentInscriptionMonth,
                'classInferredStartMonth' => $classInferredStartMonth
            ]);

            // Step 2: Detect changements (Archived in this class + Active in another)
            $changementsByGroup = $this->detectChangements($classArchivedStudents, $classActiveStudents, $studentClasses);

            // Step 3: Detect quittants (Canceled students from LIST_STUDENT_CANCELED)
            $quittantsByGroup = $this->detectQuittantsFromStatus($classCanceledStudents, $studentClasses);

            // Step 4: Assign to groups, collect student lists
            $groups = [];
            foreach ($displayClasses as $c) {
                $cid     = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
                $name    = (string) ($c['NAME'] ?? $c['REFERENCE'] ?? "#{$cid}");
                $actifs  = (int) ($c['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0);

                $debutStudents = [];
                $ajoutStudents = [];
                $quittantStudents = [];
                $changementStudents = [];
                $activeStudents = $classActiveStudents[$cid] ?? [];

                // ALWAYS use inferred start month (earliest payment in the class) as class start month!
                $classStartYm = $classInferredStartMonth[$cid] ?? $classStartMonth[$cid] ?? null;

                // Iterate over ALL students in the class (not just those with allocations)
                $allStudents = $classAllStudents[$cid] ?? [];
                foreach ($allStudents as $sid) {
                    $firstYm = $studentFirstPaymentMonth[$sid][$cid] ?? null;
                    $inscriptionYm = $studentInscriptionMonth[$sid][$cid] ?? null;
                    $hasInscription = isset($inscriptionServices[$sid][$cid]);

                    // Business Rule 1 & 2: Début vs Ajouts
                    // Début ONLY if:
                    // 1. Non-inscription first payment month === class start month, OR
                    // 2. BOTH inscription AND non-inscription payment in class start month
                    if ($firstYm && $classStartYm) {
                        if ($firstYm === $classStartYm) {
                            $debutStudents[] = $sid;
                        } else {
                            $ajoutStudents[] = $sid;
                        }
                    } elseif ($hasInscription && $firstYm && $classStartYm) {
                        if ($firstYm === $classStartYm) {
                            $debutStudents[] = $sid;
                        } else {
                            $ajoutStudents[] = $sid;
                        }
                    } elseif ($hasInscription && !$firstYm) {
                        // Only inscription payment: NOT Début unless they also have a non-inscription payment (which they don't)
                        $ajoutStudents[] = $sid;
                    } elseif ($firstYm && !$classStartYm) {
                        // No class start date, assume first payment is Début
                        $debutStudents[] = $sid;
                    }

                    if (isset($quittantsByGroup[$cid][$sid])) {
                        $quittantStudents[] = $sid;
                    }

                    if (isset($changementsByGroup[$cid][$sid])) {
                        $changementStudents[] = $sid;
                    }
                }

                // Helper to get student names from IDs
                $getStudentListWithNames = function (array $studentIds) use ($studentNames) {
                    return array_map(function ($sid) use ($studentNames) {
                        return [
                            'id' => $sid,
                            'name' => $studentNames[$sid] ?? "Student #{$sid}"
                        ];
                    }, $studentIds);
                };

                $groups[] = [
                    'class_id'    => $cid,
                    'name'        => $name,
                    'start_date'  => $c['START_DATE'] ?? null,
                    'end_date'    => $c['END_DATE'] ?? null,
                    'debuts'      => count($debutStudents),
                    'debut_students' => $getStudentListWithNames($debutStudents),
                    'ajouts'      => count($ajoutStudents),
                    'ajout_students' => $getStudentListWithNames($ajoutStudents),
                    'quittants'   => count($quittantStudents),
                    'quittant_students' => $getStudentListWithNames($quittantStudents),
                    'changements' => count($changementStudents),
                    'changement_students' => $getStudentListWithNames($changementStudents),
                    'actifs'      => count($activeStudents),
                    'active_students' => $getStudentListWithNames($activeStudents),
                ];
            }

            usort($groups, fn($a, $b) => strcmp($a['name'], $b['name']));

            $totals = [
                'debuts'      => array_sum(array_column($groups, 'debuts')),
                'ajouts'      => array_sum(array_column($groups, 'ajouts')),
                'quittants'   => array_sum(array_column($groups, 'quittants')),
                'changements' => array_sum(array_column($groups, 'changements')),
                'actifs'      => array_sum(array_column($groups, 'actifs')),
                'groups'      => count($groups),
            ];

            $diag = [
                'classes_fetched'       => count($displayClasses),
                'all_classes_fetched'   => count($allClasses),
                'classes_with_start'    => count($classStartMonth),
                'classes_with_end'      => count($classEndDate),
                'fetch_window'          => $earliestClassStartDate . ' → ' . $endDate,
                'requested_window'      => $startDate . ' → ' . $endDate,
                'allocations_fetched'   => count($allocations),
                'allocations_with_class' => 0,
                'distinct_class_ids'    => [],
                'distinct_service_types' => [],
                'student_class_pairs'   => 0,
                'total_debuts_keyed'    => array_sum(array_column($groups, 'debuts')),
                'total_ajouts_keyed'    => array_sum(array_column($groups, 'ajouts')),
                'total_debuts_students' => 0,
                'total_ajouts_students' => 0,
                'sample_first_months'   => array_slice($studentFirstPaymentMonth, 0, 3, true),
                'sample_start_months'   => array_slice($classStartMonth, 0, 5, true),
                'sample_end_dates'      => array_slice($classEndDate, 0, 5, true),
                'class_inferred_starts' => $classInferredStartMonth,
                'class_all_students'    => $classAllStudents,
            ];
            foreach ($allocations as $alloc) {
                if (!empty($alloc['CLASS_ID'])) {
                    $diag['allocations_with_class']++;
                    $diag['distinct_class_ids'][(int) $alloc['CLASS_ID']] = true;
                }
                $st = $alloc['SERVICE_TYPE_NAME'] ?? null;
                if ($st) $diag['distinct_service_types'][$st] = ($diag['distinct_service_types'][$st] ?? 0) + 1;
            }
            $diag['distinct_class_ids']     = array_keys($diag['distinct_class_ids']);
            $diag['student_class_pairs']    = array_sum(array_map('count', $studentClasses));

            $diag['fetch_error']  = $this->lastFetchError;
            $diag['rate_limited'] = $this->lastFetchError === 'rate_limited'
                || \Illuminate\Support\Facades\Cache::has(
                    'crm.rate_limited:' . substr((string) (config('crm.token') ?? ''), -8),
                );

            return [
                'groups' => $groups,
                'totals' => $totals,
                'range'  => ['start' => $startDate, 'end' => $endDate],
                'diag'   => $diag,
            ];
        } catch (\Throwable $e) {
            return $this->emptyResult($startDate, $endDate, $e->getMessage());
        }
    }

    protected function emptyResult(string $startDate, string $endDate, ?string $error = null): array
    {
        $diag = [
            'classes_fetched'  => 0,
            'fetch_error'      => $error ? 'system_error' : $this->lastFetchError,
            'rate_limited'     => $this->lastFetchError === 'rate_limited',
        ];
        if ($error) {
            $diag['error_message'] = $error;
        }

        return [
            'groups' => [],
            'totals' => ['debuts' => 0, 'ajouts' => 0, 'quittants' => 0, 'changements' => 0, 'actifs' => 0, 'groups' => 0],
            'range'  => ['start' => $startDate, 'end' => $endDate],
            'diag'   => $diag,
        ];
    }

    /**
     * Last fetch error (if any). Set by fetchClasses() so compute() can tell
     * the view whether the empty result is due to rate limiting vs no data.
     */
    protected ?string $lastFetchError = null;

    /** Pull all classes for a center (page-walked via the existing client helper). */
    protected function fetchClasses(Crm $crm, ?int $strStoreId, bool $bustCache = false): array
    {
        $cacheKey = "crm.group_evolution.classes.{$strStoreId}";

        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: Using cached classes', ['count' => count($cached)]);
            return $cached;
        }

        try {
            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: Fetching classes from CRM', ['strStoreId' => $strStoreId]);

            $result = [];
            $page = 0;
            $maxPages = 20;

            while ($page < $maxPages) {
                $response = $crm->groups()->classes(
                    page: $page,
                    size: 25,
                    strStoreId: $strStoreId,
                );

                $pageRows = $response['data'] ?? [];
                \Illuminate\Support\Facades\Log::info('GroupEvolutionService: Page response', [
                    'page' => $page,
                    'count' => count($pageRows),
                ]);

                if (empty($pageRows)) {
                    break;
                }

                foreach ($pageRows as $row) {
                    $result[] = $row;
                }

                $hasNext = $response['pagination']['hasNext'] ?? $response['pagination']['hasMore'] ?? false;
                if (!$hasNext) {
                    break;
                }

                $page++;
            }

            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: CRM API returned classes', ['count' => count($result)]);

            Cache::put($cacheKey, $result, self::CACHE_TTL);
            return $result;
        } catch (CrmException $e) {
            \Illuminate\Support\Facades\Log::error('GroupEvolutionService: CrmException fetching classes', ['status' => $e->status, 'message' => $e->getMessage()]);
            $this->lastFetchError = $e->status === 429 ? 'rate_limited' : ('http_' . $e->status);
            return [];
        } catch (\Throwable $t) {
            \Illuminate\Support\Facades\Log::error('GroupEvolutionService: Throwable fetching classes', ['message' => $t->getMessage(), 'trace' => $t->getTraceAsString()]);
            $this->lastFetchError = 'unknown';
            return [];
        }
    }

    /**
     * Pull payment allocations EXACTLY like PaymentMatrixBuilder does
     */
    protected function fetchAllocations(Crm $crm, ?int $strStoreId, ?array $classIds = null, ?array $studentIds = null): array
    {
        try {
            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: Fetching allocations like PaymentMatrixBuilder', [
                'storeId' => $strStoreId,
                'classIds' => $classIds,
                'studentIds' => $studentIds
            ]);

            $baseQuery = array_filter([
                'strStoreId' => $strStoreId,
            ], fn($v) => $v !== null);

            $allocRows = [];

            // If only one class ID, try to use classId filter server-side like PaymentMatrixBuilder does
            if ($classIds && count($classIds) === 1) {
                try {
                    $allocRows = $crm->client()->pagedScan(
                        path: '/api/external/v1/payment-allocations',
                        baseQuery: $baseQuery + ['classId' => reset($classIds)],
                        pageSize: 25,
                        maxPages: 40,
                        concurrency: 2,
                        interBatchDelayMs: 400,
                    );
                    \Illuminate\Support\Facades\Log::info('GroupEvolutionService: All allocations fetched using single classId filter', ['count' => count($allocRows)]);
                    return $allocRows;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("GroupEvolutionService: Failed to fetch with single classId filter, falling back", [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Fallback: full scan
            try {
                $allocRows = $crm->client()->pagedScan(
                    path: '/api/external/v1/payment-allocations',
                    baseQuery: $baseQuery,
                    pageSize: 25,
                    maxPages: 40,
                    concurrency: 2,
                    interBatchDelayMs: 400,
                );

                // Filter if needed
                if ($classIds || $studentIds) {
                    $allowClassIds = $classIds ? array_flip($classIds) : null;
                    $allowStudentIds = $studentIds ? array_flip($studentIds) : null;

                    $allocRows = array_filter($allocRows, function ($row) use ($allowClassIds, $allowStudentIds) {
                        $classMatch = !$allowClassIds || isset($allowClassIds[(int) ($row['CLASS_ID'] ?? 0)]);
                        $studentMatch = !$allowStudentIds || isset($allowStudentIds[(int) ($row['STUDENT_ID'] ?? 0)]);
                        return $classMatch && $studentMatch;
                    });
                }
            } catch (\Throwable $e2) {
                \Illuminate\Support\Facades\Log::warning("GroupEvolutionService: Failed to fetch even full scan", [
                    'message' => $e2->getMessage(),
                    'trace' => $e2->getTraceAsString(),
                ]);
                $allocRows = [];
            }

            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: Final allocations count', ['count' => count($allocRows)]);
            return $allocRows;
        } catch (\Throwable $t) {
            \Illuminate\Support\Facades\Log::error('GroupEvolutionService: Fetch allocations error', ['message' => $t->getMessage(), 'trace' => $t->getTraceAsString()]);
            return [];
        }
    }

    /**
     * Same-student-in-multiple-classes → transfer (using LIST_STUDENT_ARCHIVED and LIST_STUDENT_ACTIVE).
     */
    protected function detectChangements(array $classArchivedStudents, array $classActiveStudents, array $studentClasses): array
    {
        $result = [];

        foreach ($studentClasses as $sid => $studentClassIds) {
            // Find classes where student is archived
            $archivedInClasses = [];
            foreach ($classArchivedStudents as $classId => $archivedIds) {
                if (in_array($sid, $archivedIds, true)) {
                    $archivedInClasses[] = $classId;
                }
            }

            if (empty($archivedInClasses)) continue;

            // Check if student is active in any other class
            $activeInOtherClass = false;
            foreach ($classActiveStudents as $classId => $activeIds) {
                if (in_array($sid, $activeIds, true) && !in_array($classId, $archivedInClasses, true)) {
                    $activeInOtherClass = true;
                    break;
                }
            }

            if ($activeInOtherClass) {
                // Count this student as Changement for each archived class
                foreach ($archivedInClasses as $archivedClassId) {
                    $result[$archivedClassId][$sid] = true;
                }
            }
        }

        return $result;
    }

    /**
     * Detect quittants using LIST_STUDENT_CANCELED.
     */
    protected function detectQuittantsFromStatus(array $classCanceledStudents, array $studentClasses): array
    {
        $quittants = [];
        foreach ($classCanceledStudents as $classId => $canceledIds) {
            foreach ($canceledIds as $sid) {
                $quittants[$classId][$sid] = true;
            }
        }
        return $quittants;
    }

    /**
     * Parse student list JSON string into array of student IDs and collect names.
     * Handles both JSON and comma-separated lists.
     * Returns array of student IDs, and also populates $studentNames map.
     */
    protected function parseStudentList(mixed $list, array &$studentNames = []): array
    {
        $ids = [];

        if (empty($list)) {
            return $ids;
        }

        $parseItem = function ($student) use (&$studentNames) {
            $id = (int) ($student['STUDENT_ID'] ?? $student['ID'] ?? $student);
            if ($id) {
                // Build full name from first/last name, or fall back to other keys
                $firstName = trim((string) ($student['STUDENT_FIRST_NAME'] ?? ''));
                $lastName = trim((string) ($student['STUDENT_LAST_NAME'] ?? ''));
                if ($firstName || $lastName) {
                    $name = trim("{$firstName} {$lastName}");
                } else {
                    $name = (string) ($student['NAME'] ?? $student['FULL_NAME'] ?? $student['student'] ?? "Student #{$id}");
                }
                $studentNames[$id] = $name;
            }
            return $id;
        };

        if (is_array($list)) {
            $ids = array_map($parseItem, $list);
        } elseif (is_string($list)) {
            $trimmed = trim($list);
            if (!empty($trimmed)) {
                // Try parsing as JSON first
                try {
                    $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($decoded)) {
                        $ids = array_map($parseItem, $decoded);
                    }
                } catch (\Throwable) {
                    // Try comma-separated
                    $parts = explode(',', $trimmed);
                    $ids = array_map(function ($p) {
                        return (int) trim($p);
                    }, $parts);
                }
            }
        }

        return array_filter($ids, fn($id) => $id > 0);
    }

    /** Case-insensitive check for "inscription" in the service type label. */
    protected function isInscription(string $serviceType): bool
    {
        $needle = mb_strtolower($serviceType, 'UTF-8');
        return str_contains($needle, 'inscription')
            || str_contains($needle, 'frais d\'inscription');
    }
}
