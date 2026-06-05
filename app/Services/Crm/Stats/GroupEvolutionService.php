<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmGroupEvolutionSnapshot;
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
    public const CACHE_TTL = 3600; // 60 minutes

    /** Window (days) inside which a stop-then-start counts as a transfer. */
    public const CHANGEMENT_WINDOW_DAYS = 30;

    public function __construct(protected Crm $crm) {}

    /**
     * Build the full evolution report for a center + date range.
     *
     * Reads exclusively from crm_group_evolution_snapshot — zero API calls.
     * The snapshot is populated by: php artisan crm:build-group-evolution --all
     * which runs as part of: php artisan crm:sync-all (every 2 hours).
     */
    public function build(Crm $crm, ?int $strStoreId, string $startDate, string $endDate, bool $bustCache = false): array
    {
        $cacheKey = "crm.group_evolution.{$strStoreId}.{$startDate}.{$endDate}";

        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($strStoreId, $startDate, $endDate) {
            // Read from precomputed snapshot — one SELECT, < 80ms, zero API calls.
            // When multiple snapshot ranges overlap the requested window (e.g. the command
            // was run with different --months values), keep only the most-recently computed
            // row per class to avoid duplicating groups in the table.
            $rows = CrmGroupEvolutionSnapshot::query()
                ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
                ->where('range_start', '<=', $startDate)
                ->where('range_end', '>=', $endDate)
                ->orderByDesc('computed_at') // most recent first so unique() keeps the latest
                ->get()
                ->unique('class_id')         // one row per class — keeps the most recently computed
                ->sortBy('class_name');       // restore display order

            if ($rows->isEmpty()) {
                // No snapshot yet — show a helpful message instead of timing out
                $nextEvenHour = Carbon::now('Africa/Casablanca')
                    ->ceilHour(2)->format('H:i');

                return $this->emptyResult(
                    $startDate,
                    $endDate,
                    "Snapshot not yet generated. Next auto-sync: ~{$nextEvenHour}. "
                    . "Run now: php artisan crm:sync-all"
                );
            }

            $groups = $rows->map(fn ($r) => [
                'class_id'    => $r->class_id,
                'name'        => $r->class_name,
                'start_date'  => $r->class_start_date?->toDateString(),
                'end_date'    => $r->class_end_date?->toDateString(),
                'debuts'      => $r->debuts,
                'ajouts'      => $r->ajouts,
                'quittants'   => $r->quittants,
                'changements' => $r->changements,
                'actifs'      => $r->actifs,
            ])->toArray();

            return [
                'groups' => $groups,
                'totals' => [
                    'debuts'      => array_sum(array_column($groups, 'debuts')),
                    'ajouts'      => array_sum(array_column($groups, 'ajouts')),
                    'quittants'   => array_sum(array_column($groups, 'quittants')),
                    'changements' => array_sum(array_column($groups, 'changements')),
                    'actifs'      => array_sum(array_column($groups, 'actifs')),
                    'groups'      => count($groups),
                ],
                'range' => ['start' => $startDate, 'end' => $endDate],
                'diag'  => [
                    'source'      => 'local_snapshot',
                    'computed_at' => $rows->first()?->computed_at,
                    'classes_fetched' => $rows->count(),
                ],
            ];
        });
    }

    protected function emptyResult(string $startDate, string $endDate, ?string $error = null): array
    {
        return [
            'groups' => [],
            'totals' => ['debuts' => 0, 'ajouts' => 0, 'quittants' => 0, 'changements' => 0, 'actifs' => 0, 'groups' => 0],
            'range'  => ['start' => $startDate, 'end' => $endDate],
            'diag'   => [
                'source'        => 'empty',
                'error_message' => $error,
            ],
        ];
    }
}
