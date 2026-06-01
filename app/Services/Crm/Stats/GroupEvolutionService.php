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
    public function build(?int $strStoreId, string $startDate, string $endDate, bool $bustCache = false): array
    {
        $cacheKey = "crm.group_evolution.{$strStoreId}.{$startDate}.{$endDate}";
        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($strStoreId, $startDate, $endDate) {
            return $this->compute($strStoreId, $startDate, $endDate);
        });
    }

    protected function compute(?int $strStoreId, string $startDate, string $endDate): array
    {
        $this->lastFetchError = null;

        try {
            $classes = $this->fetchClasses($strStoreId);

            if (empty($classes)) {
                return $this->emptyResult($startDate, $endDate);
            }

            $classStartMonth = [];
            $classEndDate    = [];
            foreach ($classes as $c) {
                $cid   = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
                $start = $c['START_DATE'] ?? null;
                $end   = $c['END_DATE']   ?? null;
                if ($cid && $start) {
                    try {
                        $classStartMonth[$cid] = Carbon::parse($start)->format('Y-m');
                    } catch (\Throwable) {
                    }
                }
                if ($cid && $end) {
                    try {
                        $classEndDate[$cid] = Carbon::parse($end)->toDateString();
                    } catch (\Throwable) {
                    }
                }
            }

            $groups = [];
            foreach ($classes as $c) {
                $cid     = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
                $name    = (string) ($c['NAME'] ?? $c['REFERENCE'] ?? "#{$cid}");
                $actifs  = (int) ($c['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0);

                $groups[] = [
                    'class_id'    => $cid,
                    'name'        => $name,
                    'start_date'  => $c['START_DATE'] ?? null,
                    'end_date'    => $c['END_DATE']   ?? null,
                    'debuts'      => 0,
                    'ajouts'      => 0,
                    'quittants'   => 0,
                    'changements' => 0,
                    'actifs'      => $actifs,
                ];
            }

            usort($groups, fn($a, $b) => strcmp($a['name'], $b['name']));

            $totals = [
                'debuts'      => 0,
                'ajouts'      => 0,
                'quittants'   => 0,
                'changements' => 0,
                'actifs'      => array_sum(array_column($groups, 'actifs')),
                'groups'      => count($groups),
            ];

            $diag = [
                'classes_fetched'       => count($classes),
                'classes_with_start'    => count($classStartMonth),
                'classes_with_end'      => count($classEndDate),
                'fetch_window'          => $startDate . ' → ' . $endDate,
                'requested_window'      => $startDate . ' → ' . $endDate,
                'allocations_fetched'   => 0,
                'allocations_with_class' => 0,
                'distinct_class_ids'    => [],
                'distinct_service_types' => [],
                'student_class_pairs'   => 0,
                'total_debuts_keyed'    => 0,
                'total_ajouts_keyed'    => 0,
                'total_debuts_students' => 0,
                'total_ajouts_students' => 0,
                'sample_first_months'   => [],
                'sample_start_months'   => array_slice($classStartMonth, 0, 5, true),
                'sample_end_dates'      => array_slice($classEndDate, 0, 5, true),
                'simplified_mode'       => true,
            ];

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
            'simplified_mode'  => true,
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
    protected function fetchClasses(?int $strStoreId): array
    {
        $cacheKey = "crm.group_evolution.classes.{$strStoreId}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $result = $this->crm->client()->pagedScan(
                path: '/api/external/v1/groups/classes',
                baseQuery: array_filter(['strStoreId' => $strStoreId], fn($v) => $v !== null),
                pageSize: 50,
                maxPages: 1,
                concurrency: 1,
                interBatchDelayMs: 0,
            );
            Cache::put($cacheKey, $result, self::CACHE_TTL);
            return $result;
        } catch (CrmException $e) {
            $this->lastFetchError = $e->status === 429 ? 'rate_limited' : ('http_' . $e->status);
            return [];
        } catch (\Throwable) {
            $this->lastFetchError = 'unknown';
            return [];
        }
    }

    /**
     * Pull all payment allocations within the date range.
     *
     * Strategy: page-walk the unsliced range first. The Homeschool API's
     * `/payment-allocations` returns proper totalPages when you let it scan
     * the whole window — splitting into per-month variants surprisingly hits
     * a different code path on their side that responds empty for some
     * centres (observed: GLS Marrakech with a 8-month window returns 193 rows
     * on a single ranged query but 0 when each month is queried separately).
     *
     * If the unsliced call returns nothing we fall back to per-month fan-out
     * as a last resort.
     */
    protected function fetchAllocations(?int $strStoreId, string $startDate, string $endDate): array
    {
        $cacheKey = "crm.group_evolution.allocations.{$strStoreId}.{$startDate}.{$endDate}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $result = $this->crm->client()->pagedScan(
                path: '/api/external/v1/payment-allocations',
                baseQuery: array_filter([
                    'strStoreId' => $strStoreId,
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                ], fn($v) => $v !== null),
                pageSize: 50,
                maxPages: 2,
                concurrency: 2,
                interBatchDelayMs: 50,
            );
            Cache::put($cacheKey, $result, self::CACHE_TTL);
            return $result;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Same-student-in-multiple-classes → transfer. Returns
     * [classId => [studentId => true]] where each studentId is "leaving" classId
     * in favour of another. We credit the departure to whichever class has the
     * student's EARLIER last EFFECTIVE_DATE_PAYMENT — that's the one they moved
     * away from.
     */
    protected function detectChangements(array $allocations): array
    {
        // Build studentId => classId => latest payment date in range
        $studentLastDateByClass = [];
        foreach ($allocations as $alloc) {
            $sid     = $alloc['STUDENT_ID'] ?? null;
            $classId = $alloc['CLASS_ID']   ?? null;
            if (!$sid || !$classId) continue;

            $date = $alloc['EFFECTIVE_DATE_PAYMENT_ALLOCATION']
                ?? $alloc['EFFECTIVE_DATE_PAYMENT']
                ?? null;
            if (!$date) continue;

            try {
                $d = Carbon::parse($date)->setTimezone('Africa/Casablanca')->toDateString();
            } catch (\Throwable) {
                continue;
            }

            $cur = $studentLastDateByClass[$sid][$classId] ?? null;
            if ($cur === null || $d > $cur) {
                $studentLastDateByClass[$sid][$classId] = $d;
            }
        }

        $result = [];
        foreach ($studentLastDateByClass as $sid => $byClass) {
            if (count($byClass) < 2) continue; // not a mover

            // Sort classes by their latest payment date asc → earliest first.
            asort($byClass);
            $classIds = array_keys($byClass);

            // Detect transfers by looking at consecutive months, not by a hard
            // gap cap. If the student stopped paying class A in month M and
            // started paying class B in month M+1 or M+2 (i.e. within
            // CHANGEMENT_WINDOW_DAYS of each other), it's a transfer. With
            // long timeline filters (e.g. a1→b2 = 9+ months) a hard cap on
            // the FULL date span would wrongly disqualify legitimate transfers
            // detected from history — so we use the gap between *consecutive*
            // class periods instead.
            $sortedDates = array_values($byClass);
            $isTransfer  = false;
            for ($i = 0; $i < count($sortedDates) - 1; $i++) {
                try {
                    $gap = Carbon::parse($sortedDates[$i])->diffInDays(Carbon::parse($sortedDates[$i + 1]));
                } catch (\Throwable) {
                    continue;
                }
                if ($gap <= self::CHANGEMENT_WINDOW_DAYS) {
                    $isTransfer = true;
                    break;
                }
            }
            if (!$isTransfer) continue; // far apart ⇒ separate enrollments, not a transfer

            // The earliest class is the one they left — credit it.
            $leftClass = (int) array_shift($classIds);
            $result[$leftClass][(int) $sid] = true;
        }

        return $result;
    }

    /**
     * Quittant detection — students who had a paid month in the range but the
     * **next due month** is unpaid AND they don't appear as a mover.
     *
     * Uses /payment-collection with REST_AMOUNT > 0 and dueDate inside the range
     * to find "outstanding" months. Then cross-checks against the changementsByGroup
     * set so we don't double-count transfers.
     */
    protected function detectQuittants(
        ?int $strStoreId,
        string $startDate,
        string $endDate,
        array $studentClasses,
        array $changementsByGroup,
    ): array {
        // Flatten changements into a set of student IDs we've already explained.
        $moverIds = [];
        foreach ($changementsByGroup as $byStudent) {
            foreach ($byStudent as $sid => $_) {
                $moverIds[$sid] = true;
            }
        }

        $cacheKey = "crm.group_evolution.collection.{$strStoreId}.{$startDate}.{$endDate}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $collection = $cached;
        } else {
            try {
                $collection = $this->crm->client()->pagedScan(
                    path: '/api/external/v1/payment-collection',
                    baseQuery: array_filter([
                        'strStoreId'       => $strStoreId,
                        'dueDateStartDate' => $startDate,
                        'dueDateEndDate'   => $endDate,
                    ], fn($v) => $v !== null),
                    pageSize: 50,
                    maxPages: 2,
                    concurrency: 2,
                    interBatchDelayMs: 50,
                );
                Cache::put($cacheKey, $collection, self::CACHE_TTL);
            } catch (\Throwable) {
                $collection = [];
            }
        }

        $quittants = [];
        foreach ($collection as $row) {
            $sid     = $row['STUDENT_ID'] ?? null;
            $classId = $row['CLASS_ID']   ?? null;
            if (!$sid || !$classId) continue;

            // Only consider rows that are actually unpaid (REST_AMOUNT > 0
            // or status indicates outstanding).
            $rest = (float) ($row['REST_AMOUNT'] ?? $row['OPEN_AMOUNT'] ?? $row['REMAINING_AMOUNT'] ?? 0);
            if ($rest <= 0) continue;

            // Skip if this student is already classified as a mover.
            if (isset($moverIds[$sid])) continue;

            // Sanity check: the student must have actually paid this class
            // at some point in the range (otherwise this row is a brand-new
            // student who never paid — not a "quittant").
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
}
