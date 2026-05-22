<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Http\Controllers\Controller;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use App\Services\Crm\CrmLovProvider;
use App\Services\Crm\Stats\CrmStatsService;
use App\Services\Crm\Stats\DuplicateFinder;
use App\Services\Crm\Stats\InsightsService;
use App\Services\Crm\Stats\AdvancePaymentsService;
use App\Services\Crm\Stats\GroupEvolutionService;
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
        protected CrmLovProvider $lovs,
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
     * JSON endpoint that backs the student type-ahead in CRM filters.
     *
     * Accepts ?q=ahmed&strStoreId=42 and returns up to 25 students whose
     * first or last name contains the term. The /students endpoint accepts
     * firstName and lastName as separate filters, so we hit it twice (in
     * parallel via Http::pool would be nicer; for now sequential is fast
     * enough since each call is ~200ms and returns ≤25 rows) and merge.
     *
     * Response shape (matches the LOV provider's normalize() contract):
     *   [['id' => 123, 'name' => 'Ahmed Nadiri', 'reference' => '237SL126'], ...]
     */
    public function studentsSearch(Request $r): \Illuminate\Http\JsonResponse
    {
        $q = trim((string) $r->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);
        $crm         = $this->scopedCrm();

        $rows = [];
        $seen = [];

        // Two calls so a user can type either part of the name. Each call
        // returns at most 25 students and is cheap.
        $calls = [
            ['firstName' => $q, 'lastName'  => null],
            ['firstName' => null, 'lastName' => $q],
        ];

        foreach ($calls as $args) {
            try {
                $resp = $crm->students()->list(
                    page: 0,
                    size: 25,
                    includeTotal: false,
                    strStoreId: $strStoreId,
                    firstName: $args['firstName'],
                    lastName:  $args['lastName'],
                );
                foreach (($resp['data'] ?? []) as $row) {
                    $id = $row['ID'] ?? $row['STUDENT_ID'] ?? null;
                    if ($id === null || isset($seen[$id])) continue;
                    $seen[$id] = true;

                    $name = trim(($row['FIRST_NAME'] ?? '') . ' ' . ($row['LAST_NAME'] ?? '')) ?: ('#' . $id);
                    $rows[] = [
                        'id'        => (int) $id,
                        'name'      => $name,
                        'reference' => $row['REFERENCE'] ?? null,
                    ];
                }
            } catch (\Throwable) {
                // Ignore one failing call — we may still have results from the other.
            }
        }

        // Order: case-insensitive name asc, capped at 25 results.
        usort($rows, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        $rows = array_slice($rows, 0, 25);

        return response()->json(['data' => $rows]);
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

        // Annual summary chart — year defaults to current; clamps so the user
        // can't pull a year that's outside the configured CRM data window.
        $currentYear = (int) now()->year;
        $chartYear   = (int) $r->query('year', $currentYear);
        $chartYear   = max($currentYear - 5, min($chartYear, $currentYear + 1));

        // The active store filter (if any) — honors the same center context
        // as every other CRM page.
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $chartStore  = $this->centers->currentStoreId($urlOverride);

        $kpis  = $stats->perCenterKpis($start, $end);
        $trend = $stats->paymentsTrend(months: 6);
        $annual = $stats->annualSummary($chartYear, $chartStore);

        return $this->view('backoffice.crm.stats', [
            'kpis'        => $kpis,
            'trend'       => $trend,
            'preset'      => $preset,
            'start'       => $start,
            'end'         => $end,
            'annual'      => $annual,
            'chartYear'   => $chartYear,
            'currentYear' => $currentYear,
        ]);
    }

    public function students(Request $r): View
    {
        return $this->render(
            'backoffice.crm.students',
            fn (?int $strStoreId) => $this->scopedCrm()->students()->list(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $strStoreId,
                reference: $r->query('reference') ?: null,
                firstName: $r->query('firstName') ?: null,
                lastName:  $r->query('lastName')  ?: null,
                phoneNumber: $r->query('phoneNumber') ?: null,
                categoryId: $r->filled('categoryId') ? (int) $r->query('categoryId') : null,
                registrationStatus: $r->query('registrationStatus') ?: null,
            ),
            extra: [
                'lovCategories' => $this->lovs->categories(),
            ],
        );
    }

    public function sessionPresence(Request $r): View
    {
        $classId = $r->filled('classId') ? (int) $r->query('classId') : null;

        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);
        $crm         = $this->scopedCrm();

        // The Homeschool API only supports a single `date` filter on
        // /session-presence (no range). We expose Date début / Date fin in the
        // UI and apply them client-side after the page-walk. Defaults to the
        // current month so the matrix isn't blank on first load.
        $startDate = $r->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate   = $r->query('endDate')   ?: now()->endOfMonth()->toDateString();

        $shared = [
            'strStoreId'   => $strStoreId,
            'schoolYearId' => $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            'date'         => $r->query('date') ?: null,
            'presence'     => $r->query('presence') ?: null,
            'absence'      => $r->query('absence') ?: null,
            'classId'      => $classId,
            'studentId'    => $r->filled('studentId') ? (int) $r->query('studentId') : null,
        ];

        $payload = null;
        $error   = null;

        try {
            $students = $crm->students();

            // Without a classId we can't split sessions per group reliably (the
            // presence endpoint does not return CLASS_ID on rows). Return a
            // single page so the view can show an "instructions" empty state.
            if ($classId === null) {
                $payload = $students->sessionPresence(...[
                    'page' => (int) $r->query('page', 0),
                    'size' => (int) $r->query('size', 20),
                    ...$shared,
                ]);
            } else {
                // classId is set + date range provided → fire one parallel
                // request per day in the range. A class typically has ≤25
                // students per day, so each call fits in a single API page.
                //
                // Why per-day instead of pagedScan:
                //   pagedScan would pull ALL historical rows for the class
                //   (often 1000+) then filter client-side. For a 7-day window
                //   that's 50+ sequential API pages just to discard 95% of them.
                //   Per-day calls scale with the date window, not the class age.
                $shouldFetchByDay = $startDate && $endDate;

                $baseQuery = array_filter([
                    'strStoreId'   => $shared['strStoreId'],
                    'schoolYearId' => $shared['schoolYearId'],
                    'presence'     => $shared['presence'],
                    'absence'      => $shared['absence'],
                    'classId'      => $shared['classId'],
                    'studentId'    => $shared['studentId'],
                ], fn ($v) => $v !== null);

                if ($shouldFetchByDay) {
                    $allRows = $this->fetchPresenceByDay(
                        crm: $crm,
                        startDate: $startDate,
                        endDate: $endDate,
                        baseQuery: $baseQuery,
                    );
                    $rowsBefore = count($allRows);
                } else {
                    // Fallback (single `date` filter or no date range): use the
                    // full paged scan as before.
                    $allRows = $crm->client()->pagedScan(
                        path: '/api/external/v1/session-presence',
                        baseQuery: $baseQuery + array_filter(['date' => $shared['date']]),
                        pageSize: 25,
                        maxPages: 80,
                        concurrency: 3,
                    );
                    $rowsBefore = count($allRows);
                }

                $payload = [
                    'success'    => true,
                    'data'       => $allRows,
                    'pagination' => null,
                    'meta'       => [
                        'aggregated' => true,
                        'totalRows'  => count($allRows),
                        'rowsBefore' => $rowsBefore,
                        'strategy'   => $shouldFetchByDay ? 'per-day-parallel' : 'paged-scan',
                    ],
                ];
            }
        } catch (CrmException $e) {
            $error = $e;
        }

        return $this->view('backoffice.crm.session-presence', [
            'payload'      => $payload,
            'error'        => $error,
            'filters'      => $r->query(),
            'classOptions' => $this->lovs->classes($strStoreId),
            'lovSchoolYears' => $this->lovs->schoolYears($strStoreId),
            'startDate'    => $startDate,
            'endDate'      => $endDate,
        ]);
    }

    public function registrations(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        return $this->render(
            'backoffice.crm.registrations',
            fn (?int $sid) => $this->scopedCrm()->registrations()->list(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                reference: $r->query('reference') ?: null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                registrationStatusId: $r->filled('registrationStatusId') ? (int) $r->query('registrationStatusId') : null,
                levelSessionId: $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
                startDate: $r->query('startDate') ?: null,
                endDate: $r->query('endDate') ?: null,
            ),
            extra: [
                'lovRegistrationStatus' => $this->lovs->registrationStatuses(),
                'lovLevelSessions'      => $this->lovs->levelSessions($strStoreId),
                'lovSchoolYears'        => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function payments(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        return $this->render(
            'backoffice.crm.payments',
            fn (?int $sid) => $this->scopedCrm()->payments()->list(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                reference: $r->query('reference') ?: null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                paymentTypeId: $r->filled('paymentTypeId') ? (int) $r->query('paymentTypeId') : null,
                paymentStatusId: $r->filled('paymentStatusId') ? (int) $r->query('paymentStatusId') : null,
                paymentMethodeId: $r->filled('paymentMethodeId') ? (int) $r->query('paymentMethodeId') : null,
                startDate: $r->query('startDate') ?: null,
                endDate: $r->query('endDate') ?: null,
            ),
            extra: [
                'lovPaymentTypes'    => $this->lovs->paymentTypes(),
                'lovPaymentStatuses' => $this->lovs->paymentStatuses(),
                'lovPaymentMethods'  => $this->lovs->paymentMethods(),
                'lovSchoolYears'     => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function paymentChecks(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        return $this->render(
            'backoffice.crm.payment-checks',
            fn (?int $sid) => $this->scopedCrm()->payments()->checks(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                checkNumber: $r->query('checkNumber') ?: null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                paymentCheckStatusId: $r->filled('paymentCheckStatusId') ? (int) $r->query('paymentCheckStatusId') : null,
                bankId: $r->filled('bankId') ? (int) $r->query('bankId') : null,
                dueDateMin: $r->query('dueDateMin') ?: null,
                dueDateMax: $r->query('dueDateMax') ?: null,
            ),
            extra: [
                'lovBanks'                => $this->lovs->banks(),
                'lovPaymentCheckStatuses' => $this->lovs->paymentCheckStatuses(),
                'lovSchoolYears'          => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function paymentAllocations(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        return $this->render(
            'backoffice.crm.payment-allocations',
            fn (?int $sid) => $this->scopedCrm()->payments()->allocations(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                classId: $r->filled('classId') ? (int) $r->query('classId') : null,
                levelSessionId: $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
                registrationId: $r->filled('registrationId') ? (int) $r->query('registrationId') : null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                startDate: $r->query('startDate') ?: null,
                endDate: $r->query('endDate') ?: null,
            ),
            extra: [
                'lovClasses'       => $this->lovs->classes($strStoreId),
                'lovLevelSessions' => $this->lovs->levelSessions($strStoreId),
                'lovSchoolYears'   => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function paymentCollection(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);
        $crm         = $this->scopedCrm();

        // Default to current month if no due-date filter passed — matches the
        // CRM "Gestion des recouvrements" UX from the reference mockup.
        $dueDateStart = $r->query('dueDateStartDate') ?: now()->startOfMonth()->toDateString();
        $dueDateEnd   = $r->query('dueDateEndDate')   ?: now()->endOfMonth()->toDateString();

        // Shared filter args — used for both the displayed page and the
        // separate "all pages" total computation below.
        $filters = [
            'strStoreId'             => $strStoreId,
            'schoolYearId'           => $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            'id'                     => $r->filled('id') ? (int) $r->query('id') : null,
            'registrationId'         => $r->filled('registrationId') ? (int) $r->query('registrationId') : null,
            'registrationStatusId'   => $r->filled('registrationStatusId') ? (int) $r->query('registrationStatusId') : null,
            'subscriptionServiceId'  => $r->filled('subscriptionServiceId') ? (int) $r->query('subscriptionServiceId') : null,
            'prestationId'           => $r->filled('prestationId') ? (int) $r->query('prestationId') : null,
            'prestationTypeId'       => $r->filled('prestationTypeId') ? (int) $r->query('prestationTypeId') : null,
            'orderId'                => $r->filled('orderId') ? (int) $r->query('orderId') : null,
            'studentId'              => $r->filled('studentId') ? (int) $r->query('studentId') : null,
            'classId'                => $r->filled('classId') ? (int) $r->query('classId') : null,
            'levelSessionId'         => $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
            'levelSessionPackageId'  => $r->filled('levelSessionPackageId') ? (int) $r->query('levelSessionPackageId') : null,
            'levelSessionPackageIds' => $r->query('levelSessionPackageIds') ?: null,
            'serviceTypeIds'         => $r->query('serviceTypeIds') ?: null,
            'dueDateStartDate'       => $dueDateStart,
            'dueDateEndDate'         => $dueDateEnd,
            'startDay'               => $r->filled('startDay') ? (int) $r->query('startDay') : null,
            'endDay'                 => $r->filled('endDay') ? (int) $r->query('endDay') : null,
        ];

        $payload      = null;
        $error        = null;
        $sumRemaining = 0.0;
        $totalRowsSummed = 0;

        try {
            // 1) Paged response shown in the table.
            $payload = $crm->payments()->collection(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                includeTotal: $r->boolean('includeTotal', true),
                strStoreId: $filters['strStoreId'],
                schoolYearId: $filters['schoolYearId'],
                id: $filters['id'],
                registrationId: $filters['registrationId'],
                registrationStatusId: $filters['registrationStatusId'],
                subscriptionServiceId: $filters['subscriptionServiceId'],
                prestationId: $filters['prestationId'],
                prestationTypeId: $filters['prestationTypeId'],
                orderId: $filters['orderId'],
                studentId: $filters['studentId'],
                classId: $filters['classId'],
                levelSessionId: $filters['levelSessionId'],
                levelSessionPackageId: $filters['levelSessionPackageId'],
                levelSessionPackageIds: $filters['levelSessionPackageIds'],
                serviceTypeIds: $filters['serviceTypeIds'],
                dueDateStartDate: $filters['dueDateStartDate'],
                dueDateEndDate: $filters['dueDateEndDate'],
                startDay: $filters['startDay'],
                endDay: $filters['endDay'],
            );

            // 2) Compute the "Montant total" across ALL pages, matching the
            //    reference CRM that shows the global sum (not just current page).
            //    Use max page size (25), cap at 50 pages = 1 250 rows to avoid
            //    runaway loops if a filter is too broad.
            $amountKeys = ['REST_AMOUNT', 'OPEN_AMOUNT', 'REMAINING_AMOUNT', 'AMOUNT_DUE', 'AMOUNT'];
            $maxPages   = 50;
            $pageSize   = 25;

            for ($p = 0; $p < $maxPages; $p++) {
                $pageData = $crm->payments()->collection(
                    page: $p,
                    size: $pageSize,
                    includeTotal: false,
                    strStoreId: $filters['strStoreId'],
                    schoolYearId: $filters['schoolYearId'],
                    id: $filters['id'],
                    registrationId: $filters['registrationId'],
                    registrationStatusId: $filters['registrationStatusId'],
                    subscriptionServiceId: $filters['subscriptionServiceId'],
                    prestationId: $filters['prestationId'],
                    prestationTypeId: $filters['prestationTypeId'],
                    orderId: $filters['orderId'],
                    studentId: $filters['studentId'],
                    classId: $filters['classId'],
                    levelSessionId: $filters['levelSessionId'],
                    levelSessionPackageId: $filters['levelSessionPackageId'],
                    levelSessionPackageIds: $filters['levelSessionPackageIds'],
                    serviceTypeIds: $filters['serviceTypeIds'],
                    dueDateStartDate: $filters['dueDateStartDate'],
                    dueDateEndDate: $filters['dueDateEndDate'],
                    startDay: $filters['startDay'],
                    endDay: $filters['endDay'],
                );

                $rows = $pageData['data'] ?? [];
                if (empty($rows)) break;

                foreach ($rows as $row) {
                    foreach ($amountKeys as $k) {
                        if (isset($row[$k]) && is_numeric($row[$k])) {
                            $sumRemaining += (float) $row[$k];
                            $totalRowsSummed++;
                            break;
                        }
                    }
                }

                // Last page reached.
                if (count($rows) < $pageSize) break;
            }
        } catch (CrmException $e) {
            $error = $e;
        }

        // LOV dropdowns for the filter bar — pulled through the centralised
        // CrmLovProvider so the page-walk + caching + error swallowing is shared.
        return $this->view('backoffice.crm.payment-collection', [
            'payload'               => $payload,
            'error'                 => $error,
            'filters'               => $r->query(),
            'dueDateStart'          => $dueDateStart,
            'dueDateEnd'            => $dueDateEnd,
            'sumRemaining'          => $sumRemaining,
            'totalRowsSummed'       => $totalRowsSummed,
            'lovClasses'            => $this->lovs->classes($strStoreId),
            'lovPackages'           => $this->lovs->levelSessionPackages($strStoreId),
            'lovRegistrationStatus' => $this->lovs->registrationStatuses(),
            'lovPaymentTypes'       => $this->lovs->paymentTypes(),
        ]);
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
     * Liste des paiements de type "avance" (IS_AVANCE = Y).
     * Filtre fait en PHP car l'API n'expose pas isAvance comme query param.
     */
    public function advances(Request $r, AdvancePaymentsService $svc): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $storeId     = $this->centers->currentStoreId($urlOverride);
        $startDate   = $r->query('startDate') ?: null;
        $endDate     = $r->query('endDate') ?: null;
        $bustCache   = $r->boolean('refresh');
        $showDupes   = $r->boolean('showDuplicates'); // false = dedup, true = show all rows

        $report = $svc->list($storeId, $startDate, $endDate, $bustCache);

        // If user wants to see everything, merge duplicates back into advances
        if ($showDupes && !empty($report['duplicates'])) {
            $report['advances'] = collect($report['advances'])
                ->merge($report['duplicates'])
                ->sortByDesc('DATE_CREATION')
                ->values()
                ->all();
            // Recompute totals
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
     * Per-group evolution dashboard.
     *
     * For the active center + date range, shows new students (inscription
     * paid in range), departures (paid then missed next month), transfers
     * (same student paying a different group within 30 days), and the
     * current active count per group.
     */
    public function groupEvolution(Request $r, GroupEvolutionService $svc): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $storeId     = $this->centers->currentStoreId($urlOverride);
        $bustCache   = $r->boolean('refresh');

        // Default to current month — matches the other CRM dashboards.
        $startDate = $r->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate   = $r->query('endDate')   ?: now()->endOfMonth()->toDateString();

        $report = $svc->build($storeId, $startDate, $endDate, $bustCache);

        // Snapshot the unfiltered list before mutating $report — it feeds the
        // multi-select so the user can always see and toggle every group.
        $allGroupsForFilter = $report['groups'];

        // Apply the per-group filter AFTER the full report is built so the
        // service cache stays universal — only the slice the view sees changes.
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
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        return $this->render(
            'backoffice.crm.classes',
            fn (?int $sid) => $this->scopedCrm()->groups()->classes(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                schoolDepartmentId: $r->filled('schoolDepartmentId') ? (int) $r->query('schoolDepartmentId') : null,
                schoolStageId: $r->filled('schoolStageId') ? (int) $r->query('schoolStageId') : null,
                schoolLevelId: $r->filled('schoolLevelId') ? (int) $r->query('schoolLevelId') : null,
                employeeTeacherId: $r->filled('employeeTeacherId') ? (int) $r->query('employeeTeacherId') : null,
                statusId: $r->filled('statusId') ? (int) $r->query('statusId') : null,
                history: $r->query('history') ?: null,
            ),
            extra: [
                'lovSchoolLevels'      => $this->lovs->schoolLevels($strStoreId),
                'lovTeachers'          => $this->lovs->teachers($strStoreId),
                'lovClassStatuses'     => $this->lovs->classStatuses($strStoreId),
                'lovSchoolYears'       => $this->lovs->schoolYears($strStoreId),
                'lovSchoolDepartments' => $this->lovs->schoolDepartments($strStoreId),
                'lovSchoolStages'      => $this->lovs->schoolStages($strStoreId),
            ],
        );
    }

    public function groupsLevelSessions(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        return $this->render(
            'backoffice.crm.level-sessions',
            fn (?int $sid) => $this->scopedCrm()->groups()->levelSessions(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            ),
            extra: [
                'lovSchoolYears' => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function subscriptionServices(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        return $this->render(
            'backoffice.crm.subscription-services',
            fn (?int $sid) => $this->scopedCrm()->subscriptionServices()->list(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                id: $r->filled('id') ? (int) $r->query('id') : null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                registrationId: $r->filled('registrationId') ? (int) $r->query('registrationId') : null,
                dueDate: $r->query('dueDate') ?: null,
                levelSessionPackageId: $r->filled('levelSessionPackageId') ? (int) $r->query('levelSessionPackageId') : null,
                subscriptionServiceStatusId: $r->filled('subscriptionServiceStatusId') ? (int) $r->query('subscriptionServiceStatusId') : null,
                levelSessionId: $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
            ),
            extra: [
                'lovLevelSessions' => $this->lovs->levelSessions($strStoreId),
                'lovPackages'      => $this->lovs->levelSessionPackages($strStoreId),
                'lovSchoolYears'   => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    /**
     * JSON: per-class payment matrix.
     *
     * For the given classId, builds the same grid as the reference CRM's
     * "Statistique de groupe" modal:
     *   - rows  = active students (sent by the browser, already in memory)
     *   - cols  = distinct SERVICE_TYPE_NAME labels across all rows
     *   - cells = { paid, total, status } where status ∈ paid|partial|unpaid|na
     *
     * Data source: /subscription-services. The browser already has the class
     * row JSON (parsed from the table data attribute), so it POSTs the list
     * of {STUDENT_ID, FIRST_NAME, LAST_NAME, ...} — saves a paginated re-scan
     * of /groups/classes that can't even resolve the row when a filter is
     * active (e.g. statusId=2 hides historical classes).
     */
    public function classPaymentMatrix(Request $r, int $classId): \Illuminate\Http\JsonResponse
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);
        $crm         = $this->scopedCrm();

        try {
            // 1) Students come from the browser — we already loaded the class
            //    row when rendering the table, so re-fetching from the API
            //    would be wasteful (and breaks when the active filter hides it).
            $rawStudents = $r->input('students', []);
            if (is_string($rawStudents)) {
                $rawStudents = json_decode($rawStudents, true) ?: [];
            }
            $students = [];
            foreach ((array) $rawStudents as $s) {
                if (!is_array($s) || empty($s['STUDENT_ID'])) continue;
                // Each student is tagged with its bucket on the client so the
                // matrix can paint the N°/name band per status: active=normal
                // (red only if fully unpaid), archived=grey, canceled=red.
                $bucket = (string) ($s['_bucket'] ?? 'active');
                if (!in_array($bucket, ['active', 'archived', 'canceled'], true)) {
                    $bucket = 'active';
                }
                $students[] = [
                    'student_id'      => (int) $s['STUDENT_ID'],
                    'registration_id' => isset($s['REGISTRATION_ID']) ? (int) $s['REGISTRATION_ID'] : null,
                    'reference'       => $s['STUDENT_REFERENCE'] ?? null,
                    'name'            => trim(($s['STUDENT_FIRST_NAME'] ?? '') . ' ' . ($s['STUDENT_LAST_NAME'] ?? '')) ?: ('#' . $s['STUDENT_ID']),
                    'bucket'          => $bucket,
                ];
            }

            if (empty($students)) {
                return response()->json([
                    'success'  => true,
                    'class'    => ['id' => $classId, 'name' => $r->input('className')],
                    'services' => [],
                    'students' => [],
                    'totals'   => [],
                    'meta'     => ['student_count' => 0, 'service_count' => 0, 'sub_row_count' => 0],
                ]);
            }

            // 2) Fetch payment data for the whole class with ONE paged scan.
            //    payment-allocations supports classId, so we can pull every
            //    payment row for all students of this group in 1-3 HTTP
            //    requests instead of 60+ per-student fan-out (which was
            //    tripping the upstream rate limit).
            //
            //    No subscription-services call here. The class row already
            //    carries SERVICE_LIST (what services exist for this group)
            //    on the client, so the JS can render unpaid cells from that
            //    without another API hit. The matrix's status logic below
            //    derives unpaid/partial from allocation totals vs the
            //    declared service total.
            $baseQuery = array_filter([
                'strStoreId'   => $strStoreId,
                'schoolYearId' => $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            ], fn ($v) => $v !== null);

            $allocRows = $crm->client()->pagedScan(
                path: '/api/external/v1/payment-allocations',
                baseQuery: $baseQuery + ['classId' => $classId],
                pageSize: 25,
                maxPages: 40,
                concurrency: 2,
                interBatchDelayMs: 400,
            );

            // No subscription fan-out anymore — keep $subRows empty so the
            // existing aggregation loop is a no-op, then allocations and the
            // class's SERVICE_LIST (sent from the browser as $r->input('serviceList'))
            // do all the work.
            $subRows = [];
            $allowedIds = array_flip(array_map(fn ($s) => $s['student_id'], $students));

            // SERVICE_LIST seeded from the browser — describes the canonical
            // services for this class (Frais d'inscription, Frais de Septembre,
            // …). Used to populate column labels AND total amounts so cells
            // missing from allocations render as unpaid (red, 0 DH) instead
            // of being absent.
            $rawServiceList = $r->input('serviceList', []);
            if (is_string($rawServiceList)) {
                $rawServiceList = json_decode($rawServiceList, true) ?: [];
            }
            $classServiceList = is_array($rawServiceList) ? $rawServiceList : [];

            // 4) Aggregate per student × SERVICE_TYPE_NAME. Track the earliest
            //    DUE_DATE seen for each label so we can sort chronologically
            //    afterwards (matching the reference CRM's column order:
            //    inscription → Septembre → ... → Juillet, with mid-year
            //    "Frais d'inscription B2" slotted between Février and Mars).
            $matrix      = [];
            $services    = [];
            $serviceDate = []; // label => earliest DUE_DATE string

            // 4a) Seed columns + per-student TOTAL_PRICE from the class's own
            //     SERVICE_LIST (sent by the browser). This gives every active
            //     student a row of expected dues so unpaid cells render red
            //     with the correct amount, without any per-student API call.
            //     Archived/canceled students are NOT seeded — their unpaid
            //     dues no longer apply (subscription was closed), only their
            //     historical payments (from /payment-allocations) matter.
            $activeIds = array_flip(
                array_map(
                    fn ($s) => $s['student_id'],
                    array_filter($students, fn ($s) => $s['bucket'] === 'active'),
                ),
            );
            foreach ($classServiceList as $svc) {
                if (!is_array($svc)) continue;
                $label = trim((string) ($svc['SERVICE_TYPE_NAME'] ?? ''));
                if ($label === '') continue;
                if (!isset($services[$label])) {
                    $services[$label] = count($services);
                }
                $due = $svc['DUE_DATE'] ?? null;
                if ($due) {
                    $d = substr((string) $due, 0, 10);
                    if (!isset($serviceDate[$label]) || strcmp($d, $serviceDate[$label]) < 0) {
                        $serviceDate[$label] = $d;
                    }
                }
                $price = (float) ($svc['PRICE'] ?? $svc['TOTAL_PRICE'] ?? 0);
                if ($price <= 0) continue;
                foreach ($activeIds as $sid => $_) {
                    $matrix[$sid][$label] = ['paid' => 0.0, 'total' => $price];
                }
            }

            foreach ($subRows as $row) {
                $sid     = (int) ($row['STUDENT_ID'] ?? 0);
                $label   = trim((string) ($row['SERVICE_TYPE_NAME'] ?? ''));
                if (!$sid || $label === '') continue;
                if (!isset($services[$label])) {
                    $services[$label] = count($services); // first-seen index
                }
                // Keep the minimum due date across all rows of this service.
                $due = $row['DUE_DATE'] ?? null;
                if ($due) {
                    if (!isset($serviceDate[$label]) || strcmp($due, $serviceDate[$label]) < 0) {
                        $serviceDate[$label] = $due;
                    }
                }
                $total = (float) ($row['TOTAL_PRICE'] ?? 0);
                $rest  = (float) ($row['REST_AMOUNT']  ?? 0);
                $paid  = max(0.0, $total - $rest);
                $cell  = $matrix[$sid][$label] ?? ['paid' => 0.0, 'total' => 0.0];
                $cell['paid']  += $paid;
                $cell['total'] += $total;
                $matrix[$sid][$label] = $cell;
            }

            // 4b) Layer payment-allocations on top. This is the source of
            //     truth for archived/canceled students whose subscription
            //     records were cleared but whose payments survive here.
            //
            //     Sum allocations per (student, service) first, then merge:
            //       - no subscription cell → cell paid = total = sum (green)
            //       - subscription cell    → bump paid = max(existing, sum),
            //         capped at total when total > 0
            $allocSum = []; // [studentId][label] = sum of AMOUNT
            foreach ($allocRows as $alloc) {
                $sid    = (int) ($alloc['STUDENT_ID'] ?? 0);
                $label  = trim((string) ($alloc['SERVICE_TYPE_NAME'] ?? ''));
                $amount = (float) ($alloc['AMOUNT'] ?? 0);
                if (!$sid || $label === '' || $amount <= 0) continue;
                $allocSum[$sid][$label] = ($allocSum[$sid][$label] ?? 0) + $amount;

                // Record column + earliest date (allocation date as fallback).
                if (!isset($services[$label])) {
                    $services[$label] = count($services);
                }
                $allocDate = $alloc['EFFECTIVE_DATE_PAYMENT_ALLOCATION']
                    ?? $alloc['EFFECTIVE_DATE_PAYMENT']
                    ?? null;
                if ($allocDate) {
                    $d = substr((string) $allocDate, 0, 10);
                    if (!isset($serviceDate[$label]) || strcmp($d, $serviceDate[$label]) < 0) {
                        $serviceDate[$label] = $d;
                    }
                }
            }

            // Restrict allocations to the students actually in this matrix
            // (LIST_STUDENT_ACTIVE + ARCHIVED + CANCELED). classId already
            // narrows by group, but a defensive check keeps the matrix tight.
            foreach ($allocSum as $sid => $byLabel) {
                if (!isset($allowedIds[$sid])) continue;
                foreach ($byLabel as $label => $amount) {
                    $existing = $matrix[$sid][$label] ?? null;
                    if ($existing === null) {
                        // Pure allocation: no subscription row exists.
                        // Treat the paid amount as both paid and total so the
                        // cell renders fully green (ex-students who finished
                        // paying before being archived).
                        $matrix[$sid][$label] = ['paid' => $amount, 'total' => $amount];
                    } else {
                        // Subscription exists. Use whichever paid figure is
                        // higher (allocations sometimes outrun the
                        // REST_AMOUNT update on subscription-services),
                        // capped at the subscription total.
                        $maxPaid = max($existing['paid'], $amount);
                        if ($existing['total'] > 0) {
                            $maxPaid = min($maxPaid, $existing['total']);
                        } else {
                            $existing['total'] = $amount;
                        }
                        $existing['paid'] = $maxPaid;
                        $matrix[$sid][$label] = $existing;
                    }
                }
            }

            // 5) Order columns chronologically by DUE_DATE.
            //    Inscription fees ("Frais d'inscription …") sit at their own
            //    due-date position, so a Sept-paid A1/A2 inscription appears
            //    first and a mid-year B2 inscription appears in Feb/Mar slot,
            //    exactly like the reference CRM.
            $isInscription = fn (string $label): bool =>
                stripos($label, 'inscription') !== false;

            $serviceLabels = array_keys($services);
            usort($serviceLabels, function (string $a, string $b) use ($serviceDate, $isInscription, $services) {
                $da = $serviceDate[$a] ?? '9999-12-31';
                $db = $serviceDate[$b] ?? '9999-12-31';
                $cmp = strcmp($da, $db);
                if ($cmp !== 0) return $cmp;
                // Same due date — inscription wins (it's the "anchor" service).
                $ia = $isInscription($a) ? 0 : 1;
                $ib = $isInscription($b) ? 0 : 1;
                if ($ia !== $ib) return $ia <=> $ib;
                // Stable fallback: insertion order.
                return $services[$a] <=> $services[$b];
            });
            $rows = [];
            foreach ($students as $s) {
                $cells = [];
                foreach ($serviceLabels as $label) {
                    $cell = $matrix[$s['student_id']][$label] ?? null;
                    if (!$cell) {
                        $cells[$label] = ['status' => 'na', 'paid' => 0, 'total' => 0];
                        continue;
                    }
                    $status = match (true) {
                        $cell['total'] <= 0                       => 'na',
                        $cell['paid'] >= $cell['total'] - 0.01    => 'paid',
                        $cell['paid'] <= 0.01                     => 'unpaid',
                        default                                   => 'partial',
                    };
                    $cells[$label] = [
                        'status' => $status,
                        'paid'   => round($cell['paid'], 2),
                        'total'  => round($cell['total'], 2),
                    ];
                }
                $rows[] = $s + ['cells' => $cells];
            }

            // 6) Column totals (sum of paid amounts that landed in this column).
            $totals = [];
            foreach ($serviceLabels as $label) {
                $sum = 0.0;
                foreach ($rows as $r2) {
                    if (($r2['cells'][$label]['status'] ?? null) === 'paid' || ($r2['cells'][$label]['status'] ?? null) === 'partial') {
                        $sum += (float) $r2['cells'][$label]['paid'];
                    }
                }
                $totals[$label] = round($sum, 2);
            }

            return response()->json([
                'success'  => true,
                'class'    => [
                    'id'        => $classId,
                    'name'      => $r->input('className'),
                    'reference' => $r->input('classRef'),
                    'teacher'   => $r->input('classTeacher'),
                ],
                'services' => $serviceLabels,
                'students' => $rows,
                'totals'   => $totals,
                'meta'     => [
                    'student_count'   => count($students),
                    'service_count'   => count($serviceLabels),
                    'sub_row_count'   => count($subRows),
                    'alloc_row_count' => count($allocRows),
                ],
            ]);
        } catch (CrmException $e) {
            // Translate rate-limit cool-down into a friendly French message so
            // the user knows it's a transient quota issue, not a real failure.
            $friendly = $e->status === 429
                ? 'Trop de requêtes vers le CRM. Patientez ~1 minute puis réessayez — la matrice charge beaucoup de données pour les grandes classes.'
                : $e->getMessage();
            return response()->json([
                'success' => false,
                'message' => $friendly,
                'status'  => $e->status,
            ], $e->status === 429 ? 429 : 502);
        }
    }

    /**
     * Excel (.xlsx) export of the per-class payment matrix.
     *
     * Receives the same POST payload as classPaymentMatrix() and returns a
     * styled spreadsheet that mirrors the on-screen matrix: green = paid,
     * orange = partial, red = unpaid (with 0.00 DH), grey = n.a., and a red
     * label band for students who haven't paid anything.
     */
    public function classPaymentMatrixExport(Request $r, int $classId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Re-use the JSON builder so the layout never diverges.
        $json = $this->classPaymentMatrix($r, $classId);
        $data = $json->getData(true);

        $services = $data['services'] ?? [];
        $students = $data['students'] ?? [];
        $totals   = $data['totals']   ?? [];
        $className = $data['class']['name'] ?? ('classe-' . $classId);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Statistique');

        // Color palette (RGB without #) — keep in sync with the modal CSS.
        $colors = [
            'paid'         => ['fill' => 'B3E6C2', 'font' => '14532D'],
            'partial'      => ['fill' => 'F5C98A', 'font' => '7A3E00'],
            'unpaid'       => ['fill' => 'F1A8A0', 'font' => '7F1D1D'],
            'na'           => ['fill' => 'D9D9D9', 'font' => '495057'],
            'header'       => ['fill' => 'F8F9FA', 'font' => '6C757D'],
            'band_unpaid'  => ['fill' => 'F1A8A0', 'font' => '7F1D1D'], // active fully-unpaid + canceled
            'band_archive' => ['fill' => 'D9D9D9', 'font' => '495057'], // archived
            'total'        => ['fill' => 'FFFFFF', 'font' => '000000'],
        ];

        $applyFill = function (string $cell, string $bg, string $fg) use ($sheet) {
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bg);
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB($fg);
        };

        // 1) Header row
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'Étudiant');
        foreach ($services as $i => $label) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
            $sheet->setCellValue("{$col}1", $label);
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($services) + 2);
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $applyFill($headerRange, $colors['header']['fill'], $colors['header']['font']);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 2) Student rows
        $row = 2;
        foreach ($students as $idx => $s) {
            // Pick the band color the same way the modal does:
            //   canceled            → red band
            //   archived            → grey band
            //   active, fully unpaid → red band
            //   otherwise           → no band
            $bucket = $s['bucket'] ?? 'active';
            $hasPaid = false; $hasUnpaid = false;
            foreach ($services as $label) {
                $st = $s['cells'][$label]['status'] ?? null;
                if ($st === 'paid' || $st === 'partial') $hasPaid = true;
                elseif ($st === 'unpaid')                $hasUnpaid = true;
            }
            $band = match (true) {
                $bucket === 'canceled'           => 'band_unpaid',
                $bucket === 'archived'           => 'band_archive',
                !$hasPaid && $hasUnpaid          => 'band_unpaid',
                default                          => null,
            };

            $sheet->setCellValue("A{$row}", '#' . $idx);
            $sheet->setCellValueExplicit("B{$row}", $s['name'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            if ($band !== null) {
                $applyFill("A{$row}:B{$row}", $colors[$band]['fill'], $colors[$band]['font']);
                $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            }
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            foreach ($services as $i => $label) {
                $col  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
                $cell = $s['cells'][$label] ?? ['status' => 'na'];
                $status = $cell['status'];
                $value = match ($status) {
                    'paid'    => number_format((float) $cell['total'], 2, '.', '') . ' DH',
                    'partial' => number_format((float) $cell['paid'],  2, '.', '') . ' DH',
                    'unpaid'  => '0.00 DH',
                    default   => '',
                };
                $sheet->setCellValueExplicit("{$col}{$row}", $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $c = $colors[$status] ?? $colors['na'];
                $applyFill("{$col}{$row}", $c['fill'], $c['font']);
            }

            // Row-wide formatting
            $rowRange = "A{$row}:{$lastCol}{$row}";
            $sheet->getStyle($rowRange)->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Center the numeric/status cells (cols C onwards)
            $startNumCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3);
            $sheet->getStyle("{$startNumCol}{$row}:{$lastCol}{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($rowRange)->getFont()->setSize(10)->setBold(true);
            $sheet->getRowDimension($row)->setRowHeight(20);

            $row++;
        }

        // 3) Totals row
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Total');
        $sheet->getStyle("A{$row}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        foreach ($services as $i => $label) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
            $v   = (float) ($totals[$label] ?? 0);
            $sheet->setCellValueExplicit("{$col}{$row}", number_format($v, 2, '.', '') . ' DH', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $totalRange = "A{$row}:{$lastCol}{$row}";
        $sheet->getStyle($totalRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($totalRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle($totalRange)->getBorders()->getTop()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheet->getRowDimension($row)->setRowHeight(22);

        // 4) Borders + column widths + frozen header
        $fullRange = "A1:{$lastCol}{$row}";
        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('FFFFFF'); // white grid like the modal

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(28);
        for ($i = 0; $i < count($services); $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 3);
            $sheet->getColumnDimension($col)->setWidth(18);
        }
        $sheet->freezePane('C2');

        // 5) Stream as .xlsx
        $filename = 'statistique-groupe-' . preg_replace('/[^A-Za-z0-9]+/', '-', strtolower($className)) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
        ]);
    }

    public function employeeSalaries(Request $r): View
    {
        $urlOverride = $r->filled('strStoreId') ? (int) $r->query('strStoreId') : null;
        $strStoreId  = $this->centers->currentStoreId($urlOverride);

        // Default the period to the current month/year so the page isn't blank
        // on first load — the API has no implicit "this month" filter.
        $operationMonth = $r->filled('operationMonth') ? (int) $r->query('operationMonth') : (int) now()->month;
        $operationYear  = $r->filled('operationYear')  ? (int) $r->query('operationYear')  : (int) now()->year;

        return $this->render(
            'backoffice.crm.employee-salaries',
            fn (?int $sid) => $this->scopedCrm()->employeeSalaries()->calculatedSalaryClasses(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                employeeId: $r->filled('employeeId') ? (int) $r->query('employeeId') : null,
                classId: $r->filled('classId') ? (int) $r->query('classId') : null,
                operationMonth: $operationMonth,
                operationYear: $operationYear,
            ),
            extra: [
                'lovClasses'     => $this->lovs->classes($strStoreId),
                'operationMonth' => $operationMonth,
                'operationYear'  => $operationYear,
            ],
        );
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
    protected function render(string $viewName, \Closure $call, array $extra = []): View
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

        return $this->view($viewName, array_merge([
            'payload' => $payload,
            'error'   => $error,
            'filters' => request()->query(),
        ], $extra));
    }

    /**
     * Fetch session-presence rows day-by-day in parallel for the given range.
     *
     * The /session-presence endpoint accepts a single `date` (not a range),
     * so we expand the range into one query per day and fire them all via
     * Http::pool(). For a 7-day window: ~1 round-trip instead of pagedScan's
     * 50+ sequential page fetches. Hard-capped at 62 days (≈ 2 months) to
     * avoid pathological cases.
     *
     * @param  array  $baseQuery  Shared filters applied to every per-day call
     * @return array<int, array<string,mixed>>
     */
    protected function fetchPresenceByDay(Crm $crm, string $startDate, string $endDate, array $baseQuery): array
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->startOfDay();
        } catch (\Throwable) {
            return [];
        }
        if ($end->lt($start)) {
            return [];
        }
        // Hard cap to keep the fan-out bounded even if a user passes a year-long
        // range. Beyond ~2 months the per-day strategy becomes counter-productive
        // and we should switch back to pagedScan.
        if ($start->diffInDays($end) > 62) {
            return $crm->client()->pagedScan(
                path: '/api/external/v1/session-presence',
                baseQuery: $baseQuery,
                pageSize: 25,
                maxPages: 80,
                concurrency: 3,
            );
        }

        $variants = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $variants[] = ['date' => $cursor->toDateString()];
            $cursor->addDay();
        }

        return $crm->client()->parallelFetch(
            path: '/api/external/v1/session-presence',
            baseQuery: $baseQuery,
            variantQueries: $variants,
            pageSize: 25,
            concurrency: 3,
        );
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
