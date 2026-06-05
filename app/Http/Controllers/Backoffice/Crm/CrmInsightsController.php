<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmClass;
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
     *
     * Bucket rules (from registration status + start date vs class start month):
     *   - Quittant   : status = 'Annulé'
     *   - Changement : status = 'Archive'
     *   - Début      : status = 'Active' AND registration START_DATE month == class START_DATE month
     *   - Ajout      : status = 'Active' AND registration START_DATE month is after class START_DATE month
     */
    public function groupEvolutionDrill(Request $request): JsonResponse
    {
        $classId = (int) $request->query('classId');
        if (!$classId) {
            return response()->json(['rows' => [], 'count' => 0]);
        }

        // class_id param = CLASS_ID (e.g. 9896); registrations use crm_id (e.g. 9298)
        $classRecord = CrmClass::where('class_id', $classId)->first(['crm_id', 'raw_data']);
        $classRaw    = $classRecord
            ? (is_array($classRecord->raw_data) ? $classRecord->raw_data : json_decode($classRecord->raw_data, true))
            : [];
        $classStartYm = isset($classRaw['START_DATE'])
            ? Carbon::parse($classRaw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m')
            : null;

        // registrations.crm_class_id = crm_classes.crm_id (not class_id)
        $crmId = $classRecord?->crm_id ?? $classId;

        $registrations = CrmRegistration::where('crm_class_id', $crmId)
            ->orderBy('status')
            ->get(['crm_student_id', 'status', 'date_creation', 'raw_data']);

        $rows = $registrations->map(function ($r) use ($classStartYm) {
            $raw    = is_array($r->raw_data) ? $r->raw_data : json_decode($r->raw_data, true);
            $status = $r->status;

            // Quittant/Changement and Début/Ajout are independent — a student can be
            // a founding member (Début) who later cancelled (Quittant).
            // Primary display bucket = status-based; secondary = timing-based.
            $regStartYm = isset($raw['START_DATE'])
                ? Carbon::parse($raw['START_DATE'])->setTimezone('Africa/Casablanca')->format('Y-m')
                : null;

            $timingBucket = match(true) {
                $classStartYm && $regStartYm && $regStartYm <= $classStartYm => 'debut',
                $classStartYm && $regStartYm && $regStartYm > $classStartYm  => 'ajout',
                default => $classStartYm ? 'ajout' : 'debut',
            };

            $bucket = match($status) {
                'Annulé'  => 'quittant',
                'Archive' => 'changement',
                default   => $timingBucket,
            };

            return [
                'student_id'     => $r->crm_student_id,
                'student_name'   => $raw['STUDENT_FULL_NAME'] ?? '—',
                'status'         => $status,
                'bucket'         => $bucket,
                'timing_bucket'  => $timingBucket,
                'reg_start_ym'   => $regStartYm,
                'registered_at'  => $r->date_creation
                    ? Carbon::parse($r->date_creation)->format('d/m/Y')
                    : '—',
                'start_date'     => $raw['START_DATE'] ?? null,
            ];
        });

        return response()->json([
            'rows'            => $rows,
            'count'           => $rows->count(),
            'class_start_ym'  => $classStartYm,
        ]);
    }
}
