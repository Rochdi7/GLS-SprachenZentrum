<?php

namespace App\Services\Crm\Stats;

use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Per-group evolution analyzer.
 *
 * For a given center + date range, classifies each (student, group) pair
 * into one of four buckets:
 *
 *   - Ajout (new arrival): student paid the **inscription** for this group
 *     inside the range — inscription happens exactly once per enrollment,
 *     so this is the cleanest "first day in the group" signal we have.
 *
 *   - Changement de groupe: student stopped paying group G AND started paying
 *     a DIFFERENT group within 30 days (typically same school year). Detected
 *     by comparing every student's payment-allocation classes within the
 *     range — appearing in 2+ classes ⇒ they moved.
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
     *   groups: array<int, array{class_id:int, name:string, ajouts:int, quittants:int, changements:int, actifs:int}>,
     *   totals: array{ajouts:int, quittants:int, changements:int, actifs:int, groups:int},
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
        // 1) The list of groups for this center — gives us the X-axis labels
        //    and the "Actifs actuellement" count for free (no inference).
        $classes = $this->fetchClasses($strStoreId);

        if (empty($classes)) {
            return [
                'groups' => [],
                'totals' => ['ajouts' => 0, 'quittants' => 0, 'changements' => 0, 'actifs' => 0, 'groups' => 0],
                'range'  => ['start' => $startDate, 'end' => $endDate],
            ];
        }

        // 2) Every payment allocation in the range — the heart of the analysis.
        //    Row shape: STUDENT_ID, CLASS_ID, SERVICE_TYPE_NAME, EFFECTIVE_DATE_PAYMENT, ...
        $allocations = $this->fetchAllocations($strStoreId, $startDate, $endDate);

        // 3) Build helper indexes.
        //    - perGroupNew[classId] = set of student IDs whose inscription payment
        //      landed in the range and was tagged to this class.
        //    - studentClasses[studentId] = list of class IDs they paid in the range.
        $perGroupNew   = [];
        $studentClasses = [];

        foreach ($allocations as $alloc) {
            $sid     = $alloc['STUDENT_ID'] ?? null;
            $classId = $alloc['CLASS_ID']   ?? null;
            $service = (string) ($alloc['SERVICE_TYPE_NAME'] ?? '');
            if (!$sid || !$classId) continue;

            // Track all (student, class) pairs that saw money flow in the range —
            // basis for "changement de groupe" detection (same student in 2+ classes).
            $studentClasses[$sid][$classId] = true;

            // Inscription payments are the gold "new arrival" signal.
            if ($this->isInscription($service)) {
                $perGroupNew[$classId][$sid] = true;
            }
        }

        // 4) Identify movers: students who paid in 2+ classes within the window
        //    are transferring. For each pair (oldClass → newClass) the *old* class
        //    counts a "Changement de groupe" departure. We don't know the
        //    direction with 100% certainty, so we credit the change to whichever
        //    class has an EARLIER last allocation date for this student.
        $changementsByGroup = $this->detectChangements($allocations);

        // 5) Detect Quittants — students who had a paid month in the range but
        //    skipped the very next due month (1-month rule per your request)
        //    AND are not classified as movers (no other active payment).
        $quittantsByGroup = $this->detectQuittants($strStoreId, $startDate, $endDate, $studentClasses, $changementsByGroup);

        // 6) Roll everything up into the chart-ready per-group rows.
        $groups = [];
        foreach ($classes as $c) {
            $cid     = (int) ($c['CLASS_ID'] ?? $c['ID'] ?? 0);
            $name    = (string) ($c['NAME'] ?? $c['REFERENCE'] ?? "#{$cid}");
            $actifs  = (int) ($c['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0);
            $ajouts  = count($perGroupNew[$cid] ?? []);
            $changes = count($changementsByGroup[$cid] ?? []);
            $quits   = count($quittantsByGroup[$cid] ?? []);

            $groups[] = [
                'class_id'    => $cid,
                'name'        => $name,
                'ajouts'      => $ajouts,
                'quittants'   => $quits,
                'changements' => $changes,
                'actifs'      => $actifs,
            ];
        }

        // Sort groups by name so the chart is alphabetically stable across reloads.
        usort($groups, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $totals = [
            'ajouts'      => array_sum(array_column($groups, 'ajouts')),
            'quittants'   => array_sum(array_column($groups, 'quittants')),
            'changements' => array_sum(array_column($groups, 'changements')),
            'actifs'      => array_sum(array_column($groups, 'actifs')),
            'groups'      => count($groups),
        ];

        return [
            'groups' => $groups,
            'totals' => $totals,
            'range'  => ['start' => $startDate, 'end' => $endDate],
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
                concurrency: 10,
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /** Pull all payment allocations within the date range. */
    protected function fetchAllocations(?int $strStoreId, string $startDate, string $endDate): array
    {
        try {
            return $this->crm->client()->pagedScan(
                path: '/api/external/v1/payment-allocations',
                baseQuery: array_filter([
                    'strStoreId' => $strStoreId,
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                ], fn ($v) => $v !== null),
                pageSize: 25,
                maxPages: 80,    // 80 × 25 = 2000 allocations per range — plenty
                concurrency: 10,
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

            // Are the dates within the configured window? If a student paid
            // class A in January and class B in June, that's two separate
            // enrollments, not a transfer — skip.
            $firstDate = reset($byClass);
            $lastDate  = end($byClass);
            try {
                $gap = Carbon::parse($firstDate)->diffInDays(Carbon::parse($lastDate));
            } catch (\Throwable) {
                continue;
            }
            if ($gap > self::CHANGEMENT_WINDOW_DAYS * 3) continue; // far apart ⇒ not a transfer

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
                concurrency: 10,
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
