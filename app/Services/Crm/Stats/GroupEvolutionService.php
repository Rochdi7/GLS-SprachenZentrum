<?php

namespace App\Services\Crm\Stats;

use App\Services\Crm\Crm;
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
    public const CACHE_TTL = 300; // 5 minutes — same as other CRM dashboards

    /** Window (days) inside which a stop-then-start counts as a transfer. */
    public const CHANGEMENT_WINDOW_DAYS = 30;

    public function __construct(protected Crm $crm)
    {
    }

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
        // 1) The list of groups for this center — gives us the X-axis labels,
        //    the "Actifs actuellement" count, and (critically) the START_DATE
        //    of each group which we need to detect late arrivals.
        $classes = $this->fetchClasses($strStoreId);

        if (empty($classes)) {
            return [
                'groups' => [],
                'totals' => ['debuts' => 0, 'ajouts' => 0, 'quittants' => 0, 'changements' => 0, 'actifs' => 0, 'groups' => 0],
                'range'  => ['start' => $startDate, 'end' => $endDate],
            ];
        }

        // 1b) Index each class's start month (and capture its END_DATE).
        //     classStartMonth: classId => YYYY-MM, used to split Début vs Ajout.
        //     classEndDate:    classId => YYYY-MM-DD, surfaced in diagnostics so
        //                                 you can confirm the per-group window.
        //     We DO NOT filter groups out based on END_DATE here — the user-set
        //     date range only affects which allocations are pulled, never which
        //     groups are listed.
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
                    // leave unset → student won't be classified as ajout for
                    // this group (safer default than counting everyone).
                }
            }
            if ($cid && $end) {
                try {
                    $classEndDate[$cid] = Carbon::parse($end)->toDateString();
                } catch (\Throwable) {
                    // ignore — END_DATE is informational only.
                }
            }
        }

        // 2) Every payment allocation in the range — the heart of the analysis.
        //    Row shape: STUDENT_ID, CLASS_ID, SERVICE_TYPE_NAME, EFFECTIVE_DATE_PAYMENT, ...
        $allocations = $this->fetchAllocations($strStoreId, $startDate, $endDate);

        // 3) Build helper indexes.
        //    - studentClasses[studentId] = list of class IDs they paid in the range.
        //    - studentFirstPaymentMonth[studentId][classId] = YYYY-MM of their
        //      earliest in-range payment for that class. Drives Ajout detection.
        //    - inscriptionTagged[classId] = student IDs whose service literally
        //      said "inscription" — kept as a strong-signal override.
        $studentClasses           = [];
        $studentFirstPaymentMonth = [];
        $inscriptionTagged        = [];

        foreach ($allocations as $alloc) {
            $sid     = $alloc['STUDENT_ID'] ?? null;
            $classId = $alloc['CLASS_ID']   ?? null;
            $service = (string) ($alloc['SERVICE_TYPE_NAME'] ?? '');
            $rawDate = $alloc['EFFECTIVE_DATE_PAYMENT_ALLOCATION']
                    ?? $alloc['EFFECTIVE_DATE_PAYMENT']
                    ?? null;
            if (!$sid || !$classId || !$rawDate) continue;

            try {
                $payMonth = Carbon::parse($rawDate)->format('Y-m');
            } catch (\Throwable) {
                continue;
            }

            // Track all (student, class) pairs that saw money flow in the range —
            // basis for "changement de groupe" detection.
            $studentClasses[$sid][$classId] = true;

            // Track each student's earliest payment month per class — used to
            // decide if they joined the group AFTER it started ("Ajout").
            $cur = $studentFirstPaymentMonth[$sid][$classId] ?? null;
            if ($cur === null || $payMonth < $cur) {
                $studentFirstPaymentMonth[$sid][$classId] = $payMonth;
            }

            // Strong-signal override: services explicitly tagged as inscription
            // always count as Ajout regardless of timing.
            if ($this->isInscription($service)) {
                $inscriptionTagged[$classId][$sid] = true;
            }
        }

        // 4) Compute Début vs Ajout per group based on the student's first
        //    in-range payment month vs the group's START_DATE month:
        //
        //      first month  <  start month → ignored here (mainly historical
        //                                     edits — those students started
        //                                     even before the group's official
        //                                     kickoff; they're already counted
        //                                     in current "Actifs").
        //      first month  == start month → DÉBUT (came in on day one).
        //      first month  >  start month → AJOUT (joined later).
        //
        //    Inscription-tagged services still force AJOUT classification, as
        //    a safety net for cases where the start-month signal is ambiguous.
        $perGroupDebut = [];
        $perGroupNew   = [];
        foreach ($studentFirstPaymentMonth as $sid => $byClass) {
            foreach ($byClass as $classId => $payMonth) {
                $startMonth = $classStartMonth[$classId] ?? null;
                $isTagged   = isset($inscriptionTagged[$classId][$sid]);

                if ($startMonth === null) {
                    // No start date in API — fall back to inscription-tag only.
                    if ($isTagged) {
                        $perGroupNew[$classId][$sid] = true;
                    }
                    continue;
                }

                if ($payMonth === $startMonth) {
                    $perGroupDebut[$classId][$sid] = true;
                } elseif ($payMonth > $startMonth || $isTagged) {
                    $perGroupNew[$classId][$sid] = true;
                }
                // else: payMonth < startMonth → historical / edit; skip.
            }
        }

        // 5) Identify movers: students who paid in 2+ classes within the window
        //    are transferring. For each pair (oldClass → newClass) the *old* class
        //    counts a "Changement de groupe" departure. We don't know the
        //    direction with 100% certainty, so we credit the change to whichever
        //    class has an EARLIER last allocation date for this student.
        $changementsByGroup = $this->detectChangements($allocations);

        // 6) Detect Quittants — students who had a paid month in the range but
        //    skipped the very next due month (1-month rule per your request)
        //    AND are not classified as movers (no other active payment).
        $quittantsByGroup = $this->detectQuittants($strStoreId, $startDate, $endDate, $studentClasses, $changementsByGroup);

        // 7) Roll everything up into the chart-ready per-group rows.
        $groups = [];
        foreach ($classes as $c) {
            $cid     = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
            $name    = (string) ($c['NAME'] ?? $c['REFERENCE'] ?? "#{$cid}");
            $actifs  = (int) ($c['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0);
            $debuts  = count($perGroupDebut[$cid] ?? []);
            $ajouts  = count($perGroupNew[$cid] ?? []);
            $changes = count($changementsByGroup[$cid] ?? []);
            $quits   = count($quittantsByGroup[$cid] ?? []);

            $groups[] = [
                'class_id'    => $cid,
                'name'        => $name,
                // Pull START_DATE / END_DATE straight from the API so the view
                // can show the per-group window; this is the source of truth
                // for whether a group is still running.
                'start_date'  => $c['START_DATE'] ?? null,
                'end_date'    => $c['END_DATE']   ?? null,
                'debuts'      => $debuts,
                'ajouts'      => $ajouts,
                'quittants'   => $quits,
                'changements' => $changes,
                'actifs'      => $actifs,
            ];
        }

        // Sort groups by name so the chart is alphabetically stable across reloads.
        usort($groups, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $totals = [
            'debuts'      => array_sum(array_column($groups, 'debuts')),
            'ajouts'      => array_sum(array_column($groups, 'ajouts')),
            'quittants'   => array_sum(array_column($groups, 'quittants')),
            'changements' => array_sum(array_column($groups, 'changements')),
            'actifs'      => array_sum(array_column($groups, 'actifs')),
            'groups'      => count($groups),
        ];

        // Diagnostic snapshot — included in the response so the view can show
        // it behind ?debug=1 without us guessing why all counts are zero.
        $diag = [
            'classes_fetched'       => count($classes),
            'classes_with_start'    => count($classStartMonth),
            'classes_with_end'      => count($classEndDate),
            'allocations_fetched'   => count($allocations),
            'allocations_with_class'=> 0,
            'distinct_class_ids'    => [],
            'distinct_service_types'=> [],
            'student_class_pairs'   => 0,
            // Computed bucket totals BEFORE the per-class rollup loop, so we
            // can tell whether the rollup itself is dropping data or whether
            // the classification step produced zeros.
            'total_debuts_keyed'    => count($perGroupDebut),
            'total_ajouts_keyed'    => count($perGroupNew),
            'total_debuts_students' => array_sum(array_map('count', $perGroupDebut)),
            'total_ajouts_students' => array_sum(array_map('count', $perGroupNew)),
            'sample_first_months'   => array_slice($studentFirstPaymentMonth, 0, 3, true),
            'sample_start_months'   => array_slice($classStartMonth, 0, 5, true),
            'sample_end_dates'      => array_slice($classEndDate, 0, 5, true),
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

        // Surface the rate-limit state so the diagnostic card can warn the user
        // that "0 allocations" is throttling, not missing data.
        $diag['rate_limited'] = \Illuminate\Support\Facades\Cache::has(
            'crm.rate_limited:' . substr((string) (config('crm.token') ?? ''), -8),
        );

        return [
            'groups' => $groups,
            'totals' => $totals,
            'range'  => ['start' => $startDate, 'end' => $endDate],
            'diag'   => $diag,
        ];
    }

    /** Pull all classes for a center (page-walked via the existing client helper). */
    protected function fetchClasses(?int $strStoreId): array
    {
        try {
            return $this->crm->client()->pagedScan(
                path: '/api/external/v1/groups/classes',
                baseQuery: array_filter(['strStoreId' => $strStoreId], fn ($v) => $v !== null),
                pageSize: 25,
                maxPages: 20,
                concurrency: 3,
            );
        } catch (\Throwable) {
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
        // 1) Primary path: single ranged query, page-walked via pagedScan.
        try {
            $rows = $this->crm->client()->pagedScan(
                path: '/api/external/v1/payment-allocations',
                baseQuery: array_filter([
                    'strStoreId' => $strStoreId,
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                ], fn ($v) => $v !== null),
                pageSize: 25,
                maxPages: 80,    // 80 × 25 = 2000 allocations per range — plenty
                concurrency: 3,
            );
            if (!empty($rows)) return $rows;
        } catch (\Throwable) {
            // fall through to fallback
        }

        // 2) Fallback: per-month fan-out via parallelFetch. Slower but resilient
        //    if the unsliced query was rejected by the API gateway.
        try {
            $start = Carbon::parse($startDate)->startOfMonth();
            $end   = Carbon::parse($endDate)->startOfMonth();
        } catch (\Throwable) {
            return [];
        }
        if ($end->lt($start)) return [];

        $variants = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            for ($p = 0; $p < 4; $p++) {
                $variants[] = [
                    'page'      => $p,
                    'startDate' => $cursor->copy()->startOfMonth()->toDateString(),
                    'endDate'   => $cursor->copy()->endOfMonth()->toDateString(),
                ];
            }
            $cursor->addMonth();
        }

        try {
            return $this->crm->client()->parallelFetch(
                path: '/api/external/v1/payment-allocations',
                baseQuery: array_filter(['strStoreId' => $strStoreId], fn ($v) => $v !== null),
                variantQueries: $variants,
                pageSize: 25,
                concurrency: 3,
            );
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

        try {
            // Pull unpaid collection rows whose due date falls in the range.
            $collection = $this->crm->client()->pagedScan(
                path: '/api/external/v1/payment-collection',
                baseQuery: array_filter([
                    'strStoreId'       => $strStoreId,
                    'dueDateStartDate' => $startDate,
                    'dueDateEndDate'   => $endDate,
                ], fn ($v) => $v !== null),
                pageSize: 25,
                maxPages: 80,
                concurrency: 3,
            );
        } catch (\Throwable) {
            $collection = [];
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
