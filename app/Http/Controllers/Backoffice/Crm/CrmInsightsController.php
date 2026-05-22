<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\Stats\AdvancePaymentsService;
use App\Services\Crm\Stats\GroupEvolutionService;
use App\Services\Crm\Stats\InsightsService;
use App\Services\Crm\Stats\PaymentActivityService;
use Carbon\Carbon;
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
            $report['total_amount'] = array_sum(array_map(fn ($a) => (float) ($a['AMOUNT'] ?? 0), $report['advances']));
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
     * (same student paying a different group within 30 days), and the
     * current active count per group.
     */
    public function groupEvolution(Request $r, GroupEvolutionService $svc): View
    {
        $storeId   = $this->currentStrStoreId();
        $bustCache = $r->boolean('refresh');

        $startDate = $r->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate   = $r->query('endDate')   ?: now()->endOfMonth()->toDateString();

        $report = $svc->build($storeId, $startDate, $endDate, $bustCache);

        // Snapshot the unfiltered list before mutating — feeds the multi-select.
        $allGroupsForFilter = $report['groups'];

        $selectedClassIds = collect(explode(',', (string) $r->query('classIds', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        if (!empty($selectedClassIds)) {
            $allow = array_flip($selectedClassIds);
            $filteredGroups = array_values(array_filter(
                $report['groups'],
                fn ($g) => isset($allow[$g['class_id']]),
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
}
