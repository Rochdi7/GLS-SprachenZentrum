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
    public function build(Crm $crm, ?int $strStoreId, string $startDate, string $endDate, bool $bustCache = false): array
    {
        $cacheKey = "crm.group_evolution.{$strStoreId}.{$startDate}.{$endDate}";
        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($crm, $strStoreId, $startDate, $endDate, $bustCache) {
            return $this->compute($crm, $strStoreId, $startDate, $endDate, $bustCache);
        });
    }

    protected function compute(Crm $crm, ?int $strStoreId, string $startDate, string $endDate, bool $bustCache = false): array
    {
        $this->lastFetchError = null;

        try {
            $classes = $this->fetchClasses($crm, $strStoreId, $bustCache);

            if (empty($classes)) {
                return $this->emptyResult($startDate, $endDate);
            }

            $classStartMonth = [];
            $classEndDate    = [];
            $classArchivedStudents = [];
            $classActiveStudents = [];
            $earliestStartDate = '2020-01-01'; // Default to a very early date
            foreach ($classes as $c) {
                $cid   = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
                $start = $c['START_DATE'] ?? null;
                $end   = $c['END_DATE']   ?? null;
                if ($cid && $start) {
                    try {
                        $classStartMonth[$cid] = Carbon::parse($start)->format('Y-m');
                        // Keep track of earliest start date
                        if ($start < $earliestStartDate) {
                            $earliestStartDate = $start;
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

                // Parse archived students
                if ($cid && isset($c['LIST_STUDENT_ARCHIVED'])) {
                    $classArchivedStudents[$cid] = $this->parseStudentList($c['LIST_STUDENT_ARCHIVED']);
                } else {
                    $classArchivedStudents[$cid] = [];
                }

                // Parse active students
                if ($cid && isset($c['LIST_STUDENT_ACTIVE'])) {
                    $classActiveStudents[$cid] = $this->parseStudentList($c['LIST_STUDENT_ACTIVE']);
                } else {
                    $classActiveStudents[$cid] = [];
                }
            }

            // Fetch allocations from earliest group start date to ensure we capture Début payments
            $allocStartDate = $earliestStartDate;
            $allocations = $this->fetchAllocations($crm, $strStoreId, $allocStartDate, $endDate);

            // Log samples for debugging
            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: compute debug', [
                'classStartMonth_sample' => array_slice($classStartMonth, 0, 5, true),
                'earliestStartDate' => $earliestStartDate,
                'allocations_count' => count($allocations),
                'first_alloc_sample' => empty($allocations) ? null : $allocations[0],
            ]);

            // Step 1: Build (student, class) map
            $studentClasses = []; // studentId => classId => bool
            $studentPaymentMonths = []; // studentId => classId => array of Y-m
            $inscriptionServices = []; // studentId => classId => bool

            foreach ($allocations as $alloc) {
                $sid = (int) ($alloc['STUDENT_ID'] ?? 0);
                $cid = (int) ($alloc['CLASS_ID'] ?? 0);
                if (!$sid || !$cid) continue;

                $studentClasses[$sid][$cid] = true;

                // Track all payment months for this (student, class)
                $date = $alloc['EFFECTIVE_DATE_PAYMENT_ALLOCATION']
                    ?? $alloc['EFFECTIVE_DATE_PAYMENT']
                    ?? null;
                if ($date) {
                    try {
                        $ym = Carbon::parse($date)->format('Y-m');
                        if (!isset($studentPaymentMonths[$sid][$cid])) {
                            $studentPaymentMonths[$sid][$cid] = [];
                        }
                        $studentPaymentMonths[$sid][$cid][$ym] = true;
                    } catch (\Throwable) {
                    }
                }

                // Track inscription services
                $serviceType = $alloc['SERVICE_TYPE_NAME'] ?? '';
                if ($this->isInscription($serviceType)) {
                    $inscriptionServices[$sid][$cid] = true;
                }
            }

            // Step 2: Detect changements
            $changementsByGroup = $this->detectChangements($allocations, $classArchivedStudents, $classActiveStudents, $studentClasses);

            // Step 3: Detect quittants
            $quittantsByGroup = $this->detectQuittants($crm, $strStoreId, $startDate, $endDate, $studentClasses, $changementsByGroup);

            // Step 4: Assign to groups
            $groups = [];
            foreach ($classes as $c) {
                $cid     = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
                $name    = (string) ($c['NAME'] ?? $c['REFERENCE'] ?? "#{$cid}");
                $actifs  = (int) ($c['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0);

                $debuts = 0;
                $ajouts = 0;
                $quittants = 0;
                $changements = 0;

                foreach ($studentClasses as $sid => $byClass) {
                    if (!isset($byClass[$cid])) continue;

                    $studentPaymentMonthsForClass = $studentPaymentMonths[$sid][$cid] ?? [];
                    $classStartYm = $classStartMonth[$cid] ?? null;
                    $isDebut = false;
                    $isAjout = false;

                    if (isset($inscriptionServices[$sid][$cid])) {
                        $isAjout = true;
                    } elseif ($classStartYm && !empty($studentPaymentMonthsForClass)) {
                        // Check if any payment is in group's start month
                        if (isset($studentPaymentMonthsForClass[$classStartYm])) {
                            $isDebut = true;
                        } else {
                            // All payments are after start month → Ajout
                            $isAjout = true;
                        }
                    }

                    if ($isDebut) {
                        $debuts++;
                    }
                    if ($isAjout) {
                        $ajouts++;
                    }

                    if (isset($quittantsByGroup[$cid][$sid])) {
                        $quittants++;
                    }

                    if (isset($changementsByGroup[$cid][$sid])) {
                        $changements++;
                    }
                }

                $groups[] = [
                    'class_id'    => $cid,
                    'name'        => $name,
                    'start_date'  => $c['START_DATE'] ?? null,
                    'end_date'    => $c['END_DATE']   ?? null,
                    'debuts'      => $debuts,
                    'ajouts'      => $ajouts,
                    'quittants'   => $quittants,
                    'changements' => $changements,
                    'actifs'      => $actifs,
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
                'classes_fetched'       => count($classes),
                'classes_with_start'    => count($classStartMonth),
                'classes_with_end'      => count($classEndDate),
                'fetch_window'          => $startDate . ' → ' . $endDate,
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
                'sample_payment_months'   => array_slice($studentPaymentMonths, 0, 3, true),
                'sample_start_months'   => array_slice($classStartMonth, 0, 5, true),
                'sample_end_dates'      => array_slice($classEndDate, 0, 5, true),
                'sample_archived_students'   => array_slice($classArchivedStudents, 0, 3, true),
                'sample_active_students'   => array_slice($classActiveStudents, 0, 3, true),
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
     * Pull all payment allocations within the date range.
     */
    protected function fetchAllocations(Crm $crm, ?int $strStoreId, string $startDate, string $endDate): array
    {
        try {
            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: Fetching allocations', ['storeId' => $strStoreId, 'start' => $startDate, 'end' => $endDate]);

            $result = [];
            $page = 0;
            $maxPages = 20;

            while ($page < $maxPages) {
                $response = $crm->client()->get(
                    path: '/api/external/v1/payment-allocations',
                    query: array_filter([
                        'page' => $page,
                        'size' => 25,
                        'strStoreId' => $strStoreId,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ], fn($v) => $v !== null),
                );

                $pageRows = $response['data'] ?? [];
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

            \Illuminate\Support\Facades\Log::info('GroupEvolutionService: Allocations fetched', ['count' => count($result)]);
            return $result;
        } catch (\Throwable $t) {
            \Illuminate\Support\Facades\Log::error('GroupEvolutionService: Fetch allocations error', ['message' => $t->getMessage(), 'trace' => $t->getTraceAsString()]);
            return [];
        }
    }



    /**
     * Quittant detection.
     */
    protected function detectQuittants(
        Crm $crm,
        ?int $strStoreId,
        string $startDate,
        string $endDate,
        array $studentClasses,
        array $changementsByGroup,
    ): array {
        $moverIds = [];
        foreach ($changementsByGroup as $byStudent) {
            foreach ($byStudent as $sid => $_) {
                $moverIds[$sid] = true;
            }
        }

        try {
            $result = [];
            $page = 0;
            $maxPages = 20;

            while ($page < $maxPages) {
                $response = $crm->client()->get(
                    path: '/api/external/v1/payment-collection',
                    query: array_filter([
                        'page' => $page,
                        'size' => 25,
                        'strStoreId' => $strStoreId,
                        'dueDateStartDate' => $startDate,
                        'dueDateEndDate' => $endDate,
                    ], fn($v) => $v !== null),
                );

                $pageRows = $response['data'] ?? [];
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

            $collection = $result;
        } catch (\Throwable $t) {
            \Illuminate\Support\Facades\Log::error('GroupEvolutionService: Detect quittants error', ['message' => $t->getMessage()]);
            $collection = [];
        }

        $quittants = [];
        foreach ($collection as $row) {
            $sid     = $row['STUDENT_ID'] ?? null;
            $classId = $row['CLASS_ID']   ?? null;
            if (!$sid || !$classId) continue;

            $rest = (float) ($row['REST_AMOUNT'] ?? $row['OPEN_AMOUNT'] ?? $row['REMAINING_AMOUNT'] ?? 0);
            if ($rest <= 0) continue;

            if (isset($moverIds[$sid])) continue;

            if (!isset($studentClasses[$sid][$classId])) continue;

            $quittants[$classId][$sid] = true;
        }

        return $quittants;
    }

    /** Case-insensitive check for "inscription" in the service type label. */
    protected function isInscription(string $serviceType): bool
    {
        $needle = mb_strtolower($serviceType, 'UTF-8');
        return str_contains($needle, 'inscription')
            || str_contains($needle, 'frais d\'inscription');
    }

    /**
     * Parse student list JSON string into array of student IDs.
     * Handles both JSON and comma-separated lists.
     */
    protected function parseStudentList(mixed $list): array
    {
        if (is_array($list)) {
            return array_map(fn($s) => (int) ($s['STUDENT_ID'] ?? $s['ID'] ?? $s), $list);
        }

        if (is_string($list)) {
            $trimmed = trim($list);
            if (empty($trimmed)) return [];

            // Try parsing as JSON first
            try {
                $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    return array_map(fn($s) => (int) ($s['STUDENT_ID'] ?? $s['ID'] ?? $s), $decoded);
                }
            } catch (\Throwable) {
            }

            // Try comma-separated
            $parts = explode(',', $trimmed);
            return array_map(fn($p) => (int) trim($p), $parts);
        }

        return [];
    }

    /**
     * Same-student-in-multiple-classes → transfer, now using archived list!
     */
    protected function detectChangements(array $allocations, array $classArchivedStudents, array $classActiveStudents, array $studentClasses): array
    {
        $result = [];

        // Log samples of archived/active students for debugging
        \Illuminate\Support\Facades\Log::info('GroupEvolutionService: detectChangements debug', [
            'classArchivedStudents_sample' => array_slice($classArchivedStudents, 0, 3, true),
            'classActiveStudents_sample' => array_slice($classActiveStudents, 0, 3, true),
            'studentClasses_count' => count($studentClasses),
        ]);

        // For every student
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

        \Illuminate\Support\Facades\Log::info('GroupEvolutionService: detectChangements result', [
            'changement_count' => count($result, COUNT_RECURSIVE),
            'result_sample' => array_slice($result, 0, 3, true),
        ]);

        return $result;
    }
}
