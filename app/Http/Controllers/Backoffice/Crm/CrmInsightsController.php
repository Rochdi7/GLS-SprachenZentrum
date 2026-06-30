<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmClass;
use App\Models\CrmRegistration;
use App\Models\CrmStudent;
use App\Services\Crm\Stats\AdvancePaymentsService;
use App\Services\Crm\Stats\GroupEvolutionService;
use App\Services\Crm\Stats\InsightsService;
use App\Services\Crm\Stats\PaymentActivityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Analytics + dashboard pages built on top of the CRM API:
 *   - Cash handlers, reconciliation, retention, forecast
 *   - Advances (avances) browser
 *   - Payment activity / history (daily diff + per-payment timeline)
 *   - Per-group evolution dashboard
 *
 * The heavy lifting lives in App\Services\Crm\Stats\*; this controller
 * is mostly request-parsing + parameter clamping + view binding.
 */
class CrmInsightsController extends BaseCrmController
{
    /** Insights: per-employee weekly cash-handler breakdown. */
    public function cashHandlers(Request $r, InsightsService $insights): View
    {
        $storeId   = $this->currentStrStoreId();
        $weekStart = $r->filled('week') ? Carbon::parse($r->query('week'))->startOfWeek() : null;

        return $this->view('backoffice.crm.insights.cash-handlers', [
            'report' => $insights->cashHandlers($storeId, $weekStart),
        ]);
    }

    /** Insights: daily reconciliation report per center, last N days. */
    public function reconciliation(Request $r, InsightsService $insights): View
    {
        $days = max(1, min(60, (int) $r->query('days', 14)));

        return $this->view('backoffice.crm.insights.reconciliation', [
            'report' => $insights->reconciliation($days),
        ]);
    }

    /** Insights: retention funnel per cohort (month of START_DATE). */
    public function retention(Request $r, InsightsService $insights): View
    {
        $storeId = $this->currentStrStoreId();
        $months  = max(2, min(24, (int) $r->query('months', 8)));

        return $this->view('backoffice.crm.insights.retention', [
            'report' => $insights->retention($months, $storeId),
            'months' => $months,
        ]);
    }

    /** Insights: rolling N-month revenue forecast per center. */
    public function forecast(Request $r, InsightsService $insights): View
    {
        $storeId = $this->currentStrStoreId();
        $months  = max(2, min(12, (int) $r->query('months', 6)));

        return $this->view('backoffice.crm.insights.forecast', [
            'report' => $insights->forecast($months, $storeId),
            'months' => $months,
        ]);
    }

    /**
     * Liste des paiements de type "avance" (IS_AVANCE = Y).
     * Filtre fait en PHP car l'API n'expose pas isAvance comme query param.
     */
    public function advances(Request $r, AdvancePaymentsService $svc): View
    {
        $storeId   = $this->currentStrStoreId();
        $startDate = $r->query('startDate') ?: null;
        $endDate   = $r->query('endDate') ?: null;
        $bustCache = $r->boolean('refresh');
        $showDupes = $r->boolean('showDuplicates');

        $report = $svc->list($storeId, $startDate, $endDate, $bustCache);

        if ($showDupes && !empty($report['duplicates'])) {
            $report['advances'] = collect($report['advances'])
                ->merge($report['duplicates'])
                ->sortByDesc('DATE_CREATION')
                ->values()
                ->all();
            $report['total_amount'] = array_sum(array_map(fn($a) => (float) ($a['AMOUNT'] ?? 0), $report['advances']));
        }

        return $this->view('backoffice.crm.insights.advances', [
            'report'    => $report,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'showDupes' => $showDupes,
        ]);
    }

    /**
     * Payment activity log — daily diff page.
     * Shows what changed between yesterday and today across all centers (or one).
     */
    public function paymentActivity(Request $r, PaymentActivityService $svc): View
    {
        $storeId = $this->currentStrStoreId();
        $date    = $r->query('date') ?: Carbon::today()->toDateString();

        return $this->view('backoffice.crm.insights.payment-activity', [
            'report' => $svc->dailyDiff($date, $storeId),
        ]);
    }

    /**
     * Payment activity log — per-payment full history.
     * Shows every snapshot version chronologically with diffs.
     */
    public function paymentHistory(int $paymentId, PaymentActivityService $svc): View
    {
        return $this->view('backoffice.crm.insights.payment-history', [
            'report' => $svc->paymentHistory($paymentId),
        ]);
    }

    /**
     * Per-group evolution dashboard.
     *
     * For the active center + date range, shows new students (inscription
     * paid in range), departures (paid then missed next month), transfers
     * (same student paying a different class within 30 days), and the
     * current active count per group.
     */
    public function groupEvolution(Request $r, GroupEvolutionService $svc): View
    {
        $storeId   = $this->currentStrStoreId();
        $bustCache = $r->boolean('refresh');

        $startDate = now()->startOfMonth()->toDateString();
        $endDate   = now()->toDateString();

        $report = $svc->build($this->scopedCrm(), $storeId, $startDate, $endDate, $bustCache);

        // Split finished groups out of the active view — they power the
        // "Groupes terminés" tab and must not pollute the active KPIs/totals.
        $finishedGroups = array_values(array_filter(
            $report['groups'],
            fn ($g) => !empty($g['is_finished']),
        ));
        $report['groups'] = array_values(array_filter(
            $report['groups'],
            fn ($g) => empty($g['is_finished']),
        ));
        // Recompute active totals from the active-only slice.
        $report['totals'] = $this->sumGroupTotals($report['groups']);

        // Per finished group: completion rate = terminés / (débuts + ajouts).
        $finishedGroups = array_map(function ($g) {
            $enrolled = ($g['debuts'] ?? 0) + ($g['ajouts'] ?? 0);
            $g['enrolled']        = $enrolled;
            $g['completion_rate'] = $enrolled > 0
                ? round(($g['termines'] / $enrolled) * 100, 1)
                : null;
            return $g;
        }, $finishedGroups);
        // Most recently ended first.
        usort($finishedGroups, fn ($a, $b) => ($b['end_date'] ?? '') <=> ($a['end_date'] ?? ''));

        $finishedTotals = $this->sumGroupTotals($finishedGroups);
        $finishedEnrolled = array_sum(array_column($finishedGroups, 'enrolled'));
        $finishedTotals['enrolled']        = $finishedEnrolled;
        $finishedTotals['completion_rate'] = $finishedEnrolled > 0
            ? round(($finishedTotals['termines'] / $finishedEnrolled) * 100, 1)
            : null;

        // Snapshot the unfiltered active list before mutating — feeds the multi-select.
        $allGroupsForFilter = $report['groups'];

        $selectedClassIds = collect(explode(',', (string) $r->query('classIds', '')))
            ->map(fn($v) => (int) trim($v))
            ->filter(fn($v) => $v > 0)
            ->values()
            ->all();

        if (!empty($selectedClassIds)) {
            $allow = array_flip($selectedClassIds);
            $filteredGroups = array_values(array_filter(
                $report['groups'],
                fn($g) => isset($allow[$g['class_id']]),
            ));
            // Recompute totals from the visible slice so KPI cards stay coherent.
            $report['totals'] = $this->sumGroupTotals($filteredGroups);
            $report['groups'] = $filteredGroups;
        }

        return $this->view('backoffice.crm.group-evolution', [
            'report'           => $report,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
            'allGroups'        => $allGroupsForFilter,
            'selectedClassIds' => $selectedClassIds,
            'finishedGroups'   => $finishedGroups,
            'finishedTotals'   => $finishedTotals,
        ]);
    }

    /** Sum the evolution buckets across a list of group rows. */
    private function sumGroupTotals(array $groups): array
    {
        return [
            'debuts'      => array_sum(array_column($groups, 'debuts')),
            'ajouts'      => array_sum(array_column($groups, 'ajouts')),
            'quittants'   => array_sum(array_column($groups, 'quittants')),
            'termines'    => array_sum(array_column($groups, 'termines')),
            'changements' => array_sum(array_column($groups, 'changements')),
            'actifs'      => array_sum(array_column($groups, 'actifs')),
            'groups'      => count($groups),
        ];
    }

    /**
     * AJAX drill: students registered in a given class with payment-based bucket classification.
     *
     * Début/Ajout are determined by the student's first real monthly payment month
     * (non-inscription fees) relative to the class start month — NOT by registration START_DATE.
     * Status (Active/Annulé/Archive) is shown as a separate badge and never overrides the bucket.
     */
    public function groupEvolutionDrill(Request $request): JsonResponse
    {
        $classId = (int) $request->query('classId');
        if (!$classId) {
            return response()->json(['rows' => [], 'count' => 0]);
        }

        // class_id col = CLASS_ID from API (e.g. 9867); registrations.crm_class_id = crm_id (e.g. 9272)
        $classRecord = CrmClass::where('class_id', $classId)->first(['crm_id', 'site_id', 'raw_data']);
        $classRaw    = $classRecord
            ? (is_array($classRecord->raw_data) ? $classRecord->raw_data : json_decode($classRecord->raw_data, true))
            : [];
        $classStartYm = isset($classRaw['START_DATE'])
            ? Carbon::parse($classRaw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m')
            : null;
        $classEndYm = isset($classRaw['END_DATE'])
            ? Carbon::parse($classRaw['END_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m')
            : null;
        $storeId = $classRecord?->site_id;
        $crmId   = $classRecord?->crm_id ?? $classId;

        // Registrations store crm_class_id = LEVEL_SESSION_ID ?? CLASS_ID. Most match the
        // class's crm_id (= raw_data.ID = LEVEL_SESSION_ID), but some carry the CLASS_ID
        // instead — match on either so finished/historical groups aren't dropped.
        $registrations = CrmRegistration::whereIn('crm_class_id', array_unique([$crmId, $classId]))
            ->orderBy('status')
            ->get(['crm_student_id', 'status', 'date_creation', 'raw_data']);

        // All non-inscription monthly payment months per student in this store.
        // Source: crm_payment_allocations (full history) rather than crm_payment_snapshots
        // (2-month window) so finished groups — which ended months ago — still resolve
        // their Début/Ajout buckets and the Terminé (END_DATE month) flag.
        $studentIds = $registrations->pluck('crm_student_id')->unique()->values()->all();

        $payMonthsByStudent = DB::table('crm_payment_allocations')
            ->where('crm_store_id', $storeId)
            ->where('is_inscription', 0)
            ->whereIn('student_id', $studentIds)
            ->select('student_id', 'allocation_month')
            ->distinct()
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('allocation_month')->filter()->sort()->values()->all());

        $rows = $registrations->map(function ($r) use ($classStartYm, $classEndYm, $payMonthsByStudent) {
            $raw    = is_array($r->raw_data) ? $r->raw_data : json_decode($r->raw_data, true);
            $sid    = (int) $r->crm_student_id;

            $regYm  = isset($raw['START_DATE'])
                ? Carbon::parse($raw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m')
                : $classStartYm;

            // First payment for THIS class = earliest month >= registration START_DATE
            $months        = $payMonthsByStudent->get($sid, []);
            $firstForClass = collect($months)->first(fn ($m) => $m >= $regYm);

            $paymentBucket = match(true) {
                !$classStartYm                                           => 'unpaid',
                !$firstForClass && $r->status === 'Active' && $regYm <= $classStartYm => 'debut',
                !$firstForClass                                          => 'unpaid',
                $firstForClass <= $classStartYm                         => 'debut',
                default                                                  => 'ajout',
            };

            // Terminé: paid the group's last month (END_DATE month), and not cancelled.
            $finished = $r->status !== 'Annulé'
                && $classEndYm
                && in_array($classEndYm, $months, true);

            return [
                'student_id'              => $sid,
                'student_name'            => $raw['STUDENT_FULL_NAME'] ?? '—',
                'status'                  => $r->status,
                'bucket'                  => $paymentBucket !== 'unpaid' ? $paymentBucket : null,
                'first_paid_month'        => $firstForClass,
                'has_first_month_payment' => $firstForClass !== null,
                'payment_bucket'          => $paymentBucket,
                'finished'                => $finished,
                'reg_start_ym'            => $regYm,
                'registered_at'           => $r->date_creation
                    ? Carbon::parse($r->date_creation)->format('d/m/Y')
                    : '—',
            ];
        });

        return response()->json([
            'rows'           => $rows->values(),
            'count'          => $rows->count(),
            'class_start_ym' => $classStartYm,
            'class_end_ym'   => $classEndYm,
        ]);
    }

    /**
     * AJAX drill for FINISHED groups — a per-student monthly payment grid.
     *
     * Finished/archived groups follow a different logic from active ones: the CRM
     * archives and cancels the students but KEEPS the months they paid. So instead
     * of Début/Ajout buckets, we show exactly which months each student paid
     * (the same "Statistique de groupe" grid the CRM shows).
     *
     * Source is crm_payment_allocations (full history, keyed on CLASS_ID), so every
     * student who ever paid into this class appears — even if their registration was
     * later cancelled/archived. The "Terminé" flag = paid the class END_DATE month.
     */
    public function groupEvolutionFinishedDrill(Request $request): JsonResponse
    {
        $classId = (int) $request->query('classId');
        if (!$classId) {
            return response()->json(['rows' => [], 'count' => 0, 'months' => []]);
        }

        $classRecord = CrmClass::where('class_id', $classId)->first(['site_id', 'raw_data']);
        $classRaw    = $classRecord
            ? (is_array($classRecord->raw_data) ? $classRecord->raw_data : json_decode($classRecord->raw_data, true))
            : [];
        $classStartYm = isset($classRaw['START_DATE'])
            ? Carbon::parse($classRaw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m')
            : null;
        $classEndYm = isset($classRaw['END_DATE'])
            ? Carbon::parse($classRaw['END_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m')
            : null;
        $storeId = $classRecord?->site_id;

        // All allocations for this class (every student who paid, archived or not).
        $allocs = DB::table('crm_payment_allocations')
            ->where('class_id', $classId)
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->select('student_id', 'allocation_month', 'is_inscription', 'amount')
            ->get();

        if ($allocs->isEmpty()) {
            return response()->json([
                'rows'           => [],
                'count'          => 0,
                'months'         => [],
                'class_start_ym' => $classStartYm,
                'class_end_ym'   => $classEndYm,
            ]);
        }

        // Build the ordered list of months that appear (non-inscription payments only).
        $months = $allocs->where('is_inscription', 0)
            ->pluck('allocation_month')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Resolve student names from the local students mirror.
        $studentIds = $allocs->pluck('student_id')->unique()->values()->all();
        $names = CrmStudent::whereIn('crm_id', $studentIds)
            ->get(['crm_id', 'first_name', 'last_name'])
            ->mapWithKeys(fn ($s) => [(string) $s->crm_id => trim("{$s->first_name} {$s->last_name}")]);

        // Pivot: per student → [month => total amount], plus inscription total.
        $byStudent = []; // [sid => ['months' => [ym => amount], 'inscription' => amount]]
        foreach ($allocs as $a) {
            $sid = (string) $a->student_id;
            $byStudent[$sid] ??= ['months' => [], 'inscription' => 0.0];
            if ($a->is_inscription) {
                $byStudent[$sid]['inscription'] += (float) $a->amount;
            } elseif ($a->allocation_month) {
                $byStudent[$sid]['months'][$a->allocation_month] =
                    ($byStudent[$sid]['months'][$a->allocation_month] ?? 0) + (float) $a->amount;
            }
        }

        $rows = [];
        foreach ($byStudent as $sid => $data) {
            $paidMonths = array_keys(array_filter($data['months'], fn ($v) => $v > 0));
            $rows[] = [
                'student_id'  => (int) $sid,
                'student_name'=> $names[$sid] ?? ('#' . $sid),
                'inscription' => $data['inscription'],
                'months'      => $data['months'],            // [ym => amount]
                'paid_count'  => count($paidMonths),
                'finished'    => $classEndYm && in_array($classEndYm, $paidMonths, true),
            ];
        }

        // Finished students first, then most months paid, then name.
        usort($rows, function ($a, $b) {
            return [$b['finished'], $b['paid_count'], $b['student_name']]
                <=> [$a['finished'], $a['paid_count'], $a['student_name']];
        });

        return response()->json([
            'rows'           => $rows,
            'count'          => count($rows),
            'months'         => $months,
            'class_start_ym' => $classStartYm,
            'class_end_ym'   => $classEndYm,
        ]);
    }
}
