<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmClass;
use App\Models\CrmCollectionRow;
use App\Models\CrmPaymentAllocation;
use App\Models\CrmRegistration;
use App\Services\Crm\Stats\AdvancePaymentsService;
use App\Services\Crm\Stats\GroupEvolutionService;
use App\Services\Crm\Stats\InsightsService;
use App\Services\Crm\Stats\PaymentActivityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $startDate = $r->query('startDate') ?: now()->subDays(15)->toDateString();
        $endDate   = $r->query('endDate')   ?: now()->toDateString();

        $report = $svc->build($this->scopedCrm(), $storeId, $startDate, $endDate, $bustCache);

        // Snapshot the unfiltered list before mutating — feeds the multi-select.
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
            $report['totals'] = [
                'debuts'      => array_sum(array_column($filteredGroups, 'debuts')),
                'ajouts'      => array_sum(array_column($filteredGroups, 'ajouts')),
                'quittants'   => array_sum(array_column($filteredGroups, 'quittants')),
                'changements' => array_sum(array_column($filteredGroups, 'changements')),
                'actifs'      => array_sum(array_column($filteredGroups, 'actifs')),
                'groups'      => count($filteredGroups),
            ];
            $report['groups'] = $filteredGroups;
        }

        return $this->view('backoffice.crm.group-evolution', [
            'report'           => $report,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
            'allGroups'        => $allGroupsForFilter,
            'selectedClassIds' => $selectedClassIds,
        ]);
    }

    /**
     * AJAX drill: students registered in a given class with bucket classification.
     * ?classId=123&startDate=2026-01-01&endDate=2026-06-05
     */
    public function groupEvolutionDrill(Request $request): JsonResponse
    {
        $classId   = (int) $request->query('classId');
        $startDate = $request->query('startDate') ?: now()->subDays(180)->toDateString();
        $endDate   = $request->query('endDate')   ?: now()->toDateString();

        if (!$classId) {
            return response()->json(['rows' => [], 'count' => 0]);
        }

        // --- Classify each student in this class into a bucket ---

        // 1. Payment allocations for this class in the date range
        $allocations = CrmPaymentAllocation::where('class_id', $classId)
            ->where('allocation_date', '>=', $startDate)
            ->where('allocation_date', '<=', $endDate)
            ->get(['student_id', 'allocation_month', 'is_inscription']);

        // Build per-student month sets and inscription flags
        $studentMonths    = []; // [student_id => ['YYYY-MM' => true]]
        $inscriptionFlags = []; // [student_id => true]
        foreach ($allocations as $alloc) {
            $sid = $alloc->student_id;
            $studentMonths[$sid][$alloc->allocation_month] = true;
            if ($alloc->is_inscription) {
                $inscriptionFlags[$sid] = true;
            }
        }

        // 2. Class start month + store id (single query)
        $classRecord = CrmClass::where('class_id', $classId)->first(['site_id', 'raw_data']);
        $storeId     = $classRecord ? (int) $classRecord->site_id : null;
        $classRaw    = $classRecord ? (is_array($classRecord->raw_data) ? $classRecord->raw_data : json_decode($classRecord->raw_data, true)) : [];
        $startYm     = isset($classRaw['START_DATE'])
            ? Carbon::parse($classRaw['START_DATE'])->format('Y-m')
            : null;

        // 3. Archived/active lists for changement detection
        $archivedStudents = $this->parseStudentIdList($classRaw['LIST_STUDENT_ARCHIVED'] ?? []);

        // Students archived in this class but active elsewhere = changement
        $changementIds = [];
        if (!empty($archivedStudents)) {
            $archivedSet  = array_flip($archivedStudents);
            $otherClasses = CrmClass::where('site_id', $storeId)
                ->where('class_id', '!=', $classId)
                ->whereNotNull('raw_data')
                ->get(['class_id', 'raw_data']);
            foreach ($otherClasses as $oc) {
                $ocRaw    = is_array($oc->raw_data) ? $oc->raw_data : json_decode($oc->raw_data, true);
                $ocActive = $this->parseStudentIdList($ocRaw['LIST_STUDENT_ACTIVE'] ?? []);
                foreach ($ocActive as $sid) {
                    if (isset($archivedSet[$sid])) {
                        $changementIds[$sid] = true;
                    }
                }
            }
        }

        // 4. Quittants: unpaid collection rows for this class in range, not changements
        $quittantIds = [];
        $unpaidRows  = CrmCollectionRow::where('crm_store_id', $storeId)
            ->where('class_id', $classId)
            ->where('registration_status_name', 'Active')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->where('rest_amount', '>', 0)
            ->pluck('student_id');
        foreach ($unpaidRows as $sid) {
            if (!isset($changementIds[$sid]) && isset($studentMonths[$sid])) {
                $quittantIds[$sid] = true;
            }
        }

        // 5. Assign bucket per student
        $buckets = [];
        foreach ($studentMonths as $sid => $months) {
            if (isset($changementIds[$sid])) {
                $buckets[$sid] = 'changement';
            } elseif (isset($quittantIds[$sid])) {
                $buckets[$sid] = 'quittant';
            } elseif (isset($inscriptionFlags[$sid])) {
                $buckets[$sid] = 'ajout';
            } elseif ($startYm && isset($months[$startYm])) {
                $buckets[$sid] = 'debut';
            } else {
                $buckets[$sid] = 'ajout';
            }
        }

        // --- Fetch registrations for display ---
        $rows = CrmRegistration::where('crm_class_id', $classId)
            ->orderBy('status_label')
            ->get(['crm_student_id', 'crm_class_id', 'status_label', 'date_creation', 'raw_data'])
            ->map(function ($r) use ($buckets) {
                $raw = is_array($r->raw_data) ? $r->raw_data : json_decode($r->raw_data, true);
                $sid = $r->crm_student_id;
                return [
                    'student_id'   => $sid,
                    'student_name' => $raw['STUDENT_FULL_NAME'] ?? '—',
                    'class_name'   => $raw['CLASS_NAME']        ?? '—',
                    'status'       => $r->status_label          ?? ($raw['REGISTRATION_STATUS_NAME'] ?? '—'),
                    'start_date'   => $raw['START_DATE']        ?? null,
                    'registered_at'=> $r->date_creation ? Carbon::parse($r->date_creation)->format('d/m/Y') : '—',
                    'bucket'       => $buckets[$sid] ?? null,
                ];
            });

        return response()->json(['rows' => $rows, 'count' => $rows->count()]);
    }

    private function parseStudentIdList(mixed $list): array
    {
        if (is_array($list)) {
            return array_values(array_filter(array_map(
                fn($s) => (int) (is_array($s) ? ($s['STUDENT_ID'] ?? $s['ID'] ?? 0) : $s),
                $list
            )));
        }
        if (is_string($list) && trim($list) !== '') {
            $decoded = json_decode($list, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(
                    fn($s) => (int) (is_array($s) ? ($s['STUDENT_ID'] ?? $s['ID'] ?? 0) : $s),
                    $decoded
                )));
            }
        }
        return [];
    }
}
