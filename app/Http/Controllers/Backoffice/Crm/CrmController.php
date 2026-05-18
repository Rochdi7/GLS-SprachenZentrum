<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Http\Controllers\Controller;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use App\Services\Crm\Stats\CrmStatsService;
use App\Services\Crm\Stats\DuplicateFinder;
use App\Services\Crm\Stats\InsightsService;
use App\Services\Crm\Stats\PaymentActivityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Backoffice CRM browser.
 *
 * READ-ONLY. Every method talks to the Homeschool External API v1 through the
 * service layer at App\Services\Crm. Nothing is persisted locally.
 *
 * The active center (Homeschool store) is resolved through CenterContext and
 * is automatically passed as `strStoreId` to every endpoint call.
 */
class CrmController extends Controller
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
    ) {
    }

    /** Persist the selected center in session, then redirect back. */
    public function setCenter(Request $r): RedirectResponse
    {
        $storeId = $r->input('crm_store_id');
        $this->centers->setStoreId($storeId ? (int) $storeId : null);

        return back();
    }

    public function index(): View
    {
        return $this->view('backoffice.crm.index');
    }

    /**
     * Per-center evolution dashboard: KPI snapshot + monthly payments trend.
     */
    public function stats(Request $r, CrmStatsService $stats): View
    {
        // Date range. Defaults to "this year" so we get something visible immediately.
        $preset = $r->query('range', 'year');
        [$start, $end] = match ($preset) {
            'today' => [now()->toDateString(), now()->toDateString()],
            '7d'    => [now()->subDays(7)->toDateString(),  now()->toDateString()],
            '30d'   => [now()->subDays(30)->toDateString(), now()->toDateString()],
            'year'  => [now()->startOfYear()->toDateString(), now()->toDateString()],
            'custom' => [$r->query('startDate'), $r->query('endDate')],
            default  => [null, null],
        };

        $kpis  = $stats->perCenterKpis($start, $end);
        $trend = $stats->paymentsTrend(months: 6);

        return $this->view('backoffice.crm.stats', [
            'kpis'   => $kpis,
            'trend'  => $trend,
            'preset' => $preset,
            'start'  => $start,
            'end'    => $end,
        ]);
    }

    public function students(Request $r): View
    {
        return $this->render('backoffice.crm.students', fn (?int $strStoreId) => $this->scopedCrm()->students()->list(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            reference: $r->query('reference') ?: null,
            firstName: $r->query('firstName') ?: null,
            lastName:  $r->query('lastName')  ?: null,
            phoneNumber: $r->query('phoneNumber') ?: null,
            categoryId: $r->filled('categoryId') ? (int) $r->query('categoryId') : null,
            registrationStatus: $r->query('registrationStatus') ?: null,
        ));
    }

    public function sessionPresence(Request $r): View
    {
        return $this->render('backoffice.crm.session-presence', fn (?int $strStoreId) => $this->scopedCrm()->students()->sessionPresence(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            date: $r->query('date') ?: null,
            presence: $r->query('presence') ?: null,
            absence: $r->query('absence') ?: null,
            classId: $r->filled('classId') ? (int) $r->query('classId') : null,
            studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
        ));
    }

    public function registrations(Request $r): View
    {
        return $this->render('backoffice.crm.registrations', fn (?int $strStoreId) => $this->scopedCrm()->registrations()->list(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            reference: $r->query('reference') ?: null,
            studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
            registrationStatusId: $r->filled('registrationStatusId') ? (int) $r->query('registrationStatusId') : null,
            levelSessionId: $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
            startDate: $r->query('startDate') ?: null,
            endDate: $r->query('endDate') ?: null,
        ));
    }

    public function payments(Request $r): View
    {
        return $this->render('backoffice.crm.payments', fn (?int $strStoreId) => $this->scopedCrm()->payments()->list(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            reference: $r->query('reference') ?: null,
            studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
            paymentTypeId: $r->filled('paymentTypeId') ? (int) $r->query('paymentTypeId') : null,
            paymentStatusId: $r->filled('paymentStatusId') ? (int) $r->query('paymentStatusId') : null,
            paymentMethodeId: $r->filled('paymentMethodeId') ? (int) $r->query('paymentMethodeId') : null,
            startDate: $r->query('startDate') ?: null,
            endDate: $r->query('endDate') ?: null,
        ));
    }

    public function paymentChecks(Request $r): View
    {
        return $this->render('backoffice.crm.payment-checks', fn (?int $strStoreId) => $this->scopedCrm()->payments()->checks(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            checkNumber: $r->query('checkNumber') ?: null,
            studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
            paymentCheckStatusId: $r->filled('paymentCheckStatusId') ? (int) $r->query('paymentCheckStatusId') : null,
            bankId: $r->filled('bankId') ? (int) $r->query('bankId') : null,
            dueDateMin: $r->query('dueDateMin') ?: null,
            dueDateMax: $r->query('dueDateMax') ?: null,
        ));
    }

    public function paymentAllocations(Request $r): View
    {
        return $this->render('backoffice.crm.payment-allocations', fn (?int $strStoreId) => $this->scopedCrm()->payments()->allocations(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            classId: $r->filled('classId') ? (int) $r->query('classId') : null,
            levelSessionId: $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
            registrationId: $r->filled('registrationId') ? (int) $r->query('registrationId') : null,
            studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
            startDate: $r->query('startDate') ?: null,
            endDate: $r->query('endDate') ?: null,
        ));
    }

    /**
     * Insights: per-employee weekly cash-handler breakdown.
     */
    public function cashHandlers(Request $r, InsightsService $insights): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $storeId     = $this->centers->currentStoreId($urlOverride);
        $weekStart   = $r->filled('week') ? Carbon::parse($r->query('week'))->startOfWeek() : null;

        $report = $insights->cashHandlers($storeId, $weekStart);

        return $this->view('backoffice.crm.insights.cash-handlers', [
            'report' => $report,
        ]);
    }

    /**
     * Insights: daily reconciliation report per center, last N days.
     */
    public function reconciliation(Request $r, InsightsService $insights): View
    {
        $days   = max(1, min(60, (int) $r->query('days', 14)));
        $report = $insights->reconciliation($days);

        return $this->view('backoffice.crm.insights.reconciliation', [
            'report' => $report,
        ]);
    }

    /**
     * Insights: retention funnel per cohort (month of START_DATE).
     */
    public function retention(Request $r, InsightsService $insights): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $storeId     = $this->centers->currentStoreId($urlOverride);
        $months      = max(2, min(24, (int) $r->query('months', 8)));

        $report = $insights->retention($months, $storeId);

        return $this->view('backoffice.crm.insights.retention', [
            'report' => $report,
            'months' => $months,
        ]);
    }

    /**
     * Insights: rolling 6-month revenue forecast per center.
     */
    public function forecast(Request $r, InsightsService $insights): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $storeId     = $this->centers->currentStoreId($urlOverride);
        $months      = max(2, min(12, (int) $r->query('months', 6)));

        $report = $insights->forecast($months, $storeId);

        return $this->view('backoffice.crm.insights.forecast', [
            'report' => $report,
            'months' => $months,
        ]);
    }

    /**
     * Payment activity log — daily diff page.
     * Shows what changed between yesterday and today across all centers (or one).
     */
    public function paymentActivity(Request $r, PaymentActivityService $svc): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $storeId     = $this->centers->currentStoreId($urlOverride);
        $date        = $r->query('date') ?: Carbon::today()->toDateString();

        $report = $svc->dailyDiff($date, $storeId);

        return $this->view('backoffice.crm.insights.payment-activity', [
            'report' => $report,
        ]);
    }

    /**
     * Payment activity log — per-payment full history.
     * Shows every snapshot version chronologically with diffs.
     */
    public function paymentHistory(int $paymentId, PaymentActivityService $svc): View
    {
        $report = $svc->paymentHistory($paymentId);

        return $this->view('backoffice.crm.insights.payment-history', [
            'report' => $report,
        ]);
    }

    /**
     * Duplicate-detection scan over students returned by the API.
     * Buckets by phone / WhatsApp / email / CIN / (name + store).
     */
    public function duplicates(Request $r, DuplicateFinder $finder): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $storeId     = $this->centers->currentStoreId($urlOverride);
        $bustCache   = $r->boolean('refresh');

        $report = $finder->find($storeId, bustCache: $bustCache);

        return $this->view('backoffice.crm.duplicates', [
            'report'  => $report,
            'storeId' => $storeId,
        ]);
    }

    public function lov(Request $r, string $kind): View
    {
        $method = match ($kind) {
            'school-levels'                          => 'schoolLevels',
            'registration-statuses'                  => 'registrationStatuses',
            'registration-conventions'               => 'registrationConventions',
            'registration-change-status-reasons'     => 'registrationChangeStatusReasons',
            'payment-types'                          => 'paymentTypes',
            'payment-statuses'                       => 'paymentStatuses',
            'payment-methods'                        => 'paymentMethods',
            'payment-check-statuses'                 => 'paymentCheckStatuses',
            'level-session-packages'                 => 'levelSessionPackages',
            'categories'                             => 'categories',
            'banks'                                  => 'banks',
            default                                  => abort(404),
        };

        $limit = (int) $r->query('limit', 100);

        $payload = null;
        $error   = null;
        try {
            $payload = $this->scopedCrm()->lov()->{$method}($limit);
        } catch (CrmException $e) {
            $error = $e;
        }

        return $this->view('backoffice.crm.lov', [
            'kind'    => $kind,
            'limit'   => $limit,
            'payload' => $payload,
            'error'   => $error,
        ]);
    }

    public function groupsClasses(Request $r): View
    {
        return $this->render('backoffice.crm.classes', fn (?int $strStoreId) => $this->scopedCrm()->groups()->classes(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            schoolDepartmentId: $r->filled('schoolDepartmentId') ? (int) $r->query('schoolDepartmentId') : null,
            schoolStageId: $r->filled('schoolStageId') ? (int) $r->query('schoolStageId') : null,
            schoolLevelId: $r->filled('schoolLevelId') ? (int) $r->query('schoolLevelId') : null,
            employeeTeacherId: $r->filled('employeeTeacherId') ? (int) $r->query('employeeTeacherId') : null,
            statusId: $r->filled('statusId') ? (int) $r->query('statusId') : null,
            history: $r->query('history') ?: null,
        ));
    }

    public function groupsLevelSessions(Request $r): View
    {
        return $this->render('backoffice.crm.level-sessions', fn (?int $strStoreId) => $this->scopedCrm()->groups()->levelSessions(
            page: (int) $r->query('page', 0),
            size: (int) $r->query('size', 20),
            strStoreId: $strStoreId,
            schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
        ));
    }

    /**
     * Render with center context.
     *
     * Resolves the active strStoreId, runs the API call, catches CrmException,
     * and hands the view a uniform bundle: payload, error, filters, centers,
     * currentStoreId.
     *
     * @param  \Closure(?int $strStoreId): array<string,mixed>  $call
     */
    protected function render(string $viewName, \Closure $call): View
    {
        $urlOverride = request()->filled('strStoreId') ? (int) request()->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        $payload = null;
        $error   = null;

        try {
            $payload = $call($strStoreId);
        } catch (CrmException $e) {
            $error = $e;
        }

        return $this->view($viewName, [
            'payload' => $payload,
            'error'   => $error,
            'filters' => request()->query(),
        ]);
    }

    /**
     * Get a Crm instance scoped to the active center's token, if one is set.
     * Falls back to the default Crm (using CRM_API_TOKEN from .env).
     */
    protected function scopedCrm(): Crm
    {
        $urlOverride = request()->filled('strStoreId') ? (int) request()->query('strStoreId') : null;
        $token = $this->centers->currentToken($urlOverride);
        return $this->crm->withToken($token);
    }

    /**
     * Wrap view() so every CRM page gets the center dropdown data without
     * each method needing to remember to pass it.
     */
    protected function view(string $name, array $data = []): View
    {
        return view($name, array_merge([
            'crmCenters'      => $this->centers->available(),
            'crmCurrentStore' => $this->centers->currentStoreId(),
            'crmCurrentSite'  => $this->centers->currentSite(),
        ], $data));
    }
}
