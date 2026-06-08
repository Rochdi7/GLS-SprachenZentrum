<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmPaymentSnapshot;
use App\Services\Crm\CrmException;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Payment-related CRM pages: payments list, payment checks, payment
 * allocations, and the recouvrement (collection) browser.
 */
class CrmPaymentsController extends BaseCrmController
{
    public function payments(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();
        $size = (int) $r->query('size', 5);

        // Snapshot total — Réglement only (payment_type_id = 1), filtered by
        // date_creation_date so it matches the CRM API's startDate/endDate behaviour.
        $snapshotTotal = $this->paymentsSnapshotTotal($r, $strStoreId);

        return $this->render(
            'backoffice.crm.payments',
            fn (?int $sid) => tap($this->scopedCrm()->payments()->list(
                page: (int) $r->query('page', 0),
                size: $size,
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                reference: $r->query('reference') ?: null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                paymentTypeId: $r->filled('paymentTypeId') ? (int) $r->query('paymentTypeId') : null,
                paymentStatusId: $r->filled('paymentStatusId') ? (int) $r->query('paymentStatusId') : null,
                paymentMethodeId: $r->filled('paymentMethodeId') ? (int) $r->query('paymentMethodeId') : null,
                startDate: $r->query('startDate') ?: null,
                endDate: $r->query('endDate') ?: null,
            ), function () use ($sid, $r) {
                $preloadQuery = array_filter([
                    'strStoreId' => $sid,
                    'schoolYearId' => $r->query('schoolYearId'),
                    'reference' => $r->query('reference'),
                    'studentId' => $r->query('studentId'),
                    'paymentTypeId' => $r->query('paymentTypeId'),
                    'paymentStatusId' => $r->query('paymentStatusId'),
                    'paymentMethodeId' => $r->query('paymentMethodeId'),
                    'startDate' => $r->query('startDate'),
                    'endDate' => $r->query('endDate'),
                    'size' => 20,
                ], fn($v) => $v !== null);
                $this->scopedCrm()->client()->preload('/api/external/v1/payments', $preloadQuery, 4, 0);
            }),
            extra: [
                'lovPaymentTypes'    => $this->lovs->paymentTypes(),
                'lovPaymentStatuses' => $this->lovs->paymentStatuses(),
                'lovPaymentMethods'  => $this->lovs->paymentMethods(),
                'lovSchoolYears'     => $this->lovs->schoolYears($strStoreId),
                'snapshotTotal'      => $snapshotTotal,
            ],
        );
    }

    /**
     * Compute the Réglement-only total from the local snapshot table.
     * Only runs when at least a date range is set (avoids a full-table scan).
     * Returns null when not enough filters are active to give a meaningful total.
     */
    private function paymentsSnapshotTotal(Request $r, ?int $strStoreId): ?float
    {
        $startDate = $r->query('startDate') ?: null;
        $endDate   = $r->query('endDate')   ?: null;

        // Require at least a date range to show a total.
        if (!$startDate || !$endDate) {
            return null;
        }

        // Only show Réglement total when no type filter or type filter = Réglement (id=1).
        $paymentTypeId = $r->filled('paymentTypeId') ? (int) $r->query('paymentTypeId') : null;
        if ($paymentTypeId !== null && $paymentTypeId !== 1) {
            return null;
        }

        $query = CrmPaymentSnapshot::query()
            ->where('payment_type_id', 1)
            ->whereBetween('date_creation_date', [$startDate, $endDate])
            ->whereRaw('snapshot_date = (
                SELECT MAX(s2.snapshot_date)
                FROM crm_payment_snapshots s2
                WHERE s2.crm_payment_id = crm_payment_snapshots.crm_payment_id
            )');

        if ($strStoreId) {
            $query->where('crm_store_id', $strStoreId);
        }

        if ($r->filled('studentId')) {
            $query->where('student_id', (int) $r->query('studentId'));
        }

        return (float) $query->sum('amount');
    }

    public function paymentChecks(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();
        $size = (int) $r->query('size', 5);

        return $this->render(
            'backoffice.crm.payment-checks',
            fn (?int $sid) => tap($this->scopedCrm()->payments()->checks(
                page: (int) $r->query('page', 0),
                size: $size,
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                checkNumber: $r->query('checkNumber') ?: null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                paymentCheckStatusId: $r->filled('paymentCheckStatusId') ? (int) $r->query('paymentCheckStatusId') : null,
                bankId: $r->filled('bankId') ? (int) $r->query('bankId') : null,
                dueDateMin: $r->query('dueDateMin') ?: null,
                dueDateMax: $r->query('dueDateMax') ?: null,
            ), function () use ($sid, $r) {
                $preloadQuery = array_filter([
                    'strStoreId' => $sid,
                    'schoolYearId' => $r->query('schoolYearId'),
                    'checkNumber' => $r->query('checkNumber'),
                    'studentId' => $r->query('studentId'),
                    'paymentCheckStatusId' => $r->query('paymentCheckStatusId'),
                    'bankId' => $r->query('bankId'),
                    'dueDateMin' => $r->query('dueDateMin'),
                    'dueDateMax' => $r->query('dueDateMax'),
                    'size' => 20,
                ], fn($v) => $v !== null);
                $this->scopedCrm()->client()->preload('/api/external/v1/payment-checks', $preloadQuery, 4, 0);
            }),
            extra: [
                'lovBanks'                => $this->lovs->banks(),
                'lovPaymentCheckStatuses' => $this->lovs->paymentCheckStatuses(),
                'lovSchoolYears'          => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    public function paymentAllocations(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();

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
        $strStoreId = $this->currentStrStoreId();
        $crm        = $this->scopedCrm();

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

        $payload         = null;
        $error           = null;
        $sumRemaining    = 0.0;
        $totalRowsSummed = 0;

        try {
            // 1) Paged response shown in the table.
            $pageArgs = $this->collectionArgs($filters, (int) $r->query('page', 0), (int) $r->query('size', 20), $r->boolean('includeTotal', true));
            $payload  = $crm->payments()->collection(...$pageArgs);

            // 2) "Montant total" across ALL pages (Async)
            // Generate a unique cache key based on the filters
            $filterHash = sha1(json_encode($filters));
            $cacheKey = "crm.total_sum.{$strStoreId}.{$filterHash}";
            
            $cachedSum = \Illuminate\Support\Facades\Cache::get($cacheKey);
            
            if ($cachedSum) {
                $sumRemaining = $cachedSum['sum'];
                $totalRowsSummed = $cachedSum['count'];
            } else {
                // Dispatch background computation
                \App\Jobs\Crm\ComputeCrmTotalSumJob::dispatch(
                    $filters,
                    $this->centers->currentToken($r->query('strStoreId')),
                    $cacheKey
                );
                $sumRemaining = null; // View will show "Calculating..."
                $totalRowsSummed = 0;
            }

            // Preload next page in background for snappy navigation
            $currentPage = (int) $r->query('page', 0);
            $crm->client()->preload('/api/external/v1/payment-collection', $filters, 1, $currentPage + 1);

        } catch (CrmException $e) {
            $error = $e;
        }

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
     * Spread-ready named args for Payments::collection(). Extracted because
     * the same filter set is reused twice in paymentCollection() — once for
     * the visible page and once for the cross-page total.
     *
     * PHP doesn't allow named args followed by argument unpacking at the call
     * site, so the page/size/includeTotal are folded into this helper and the
     * caller uses pure spread (...$args).
     *
     * @param  array<string, mixed>  $f
     * @return array<string, mixed>
     */
    protected function collectionArgs(array $f, int $page, int $size, bool $includeTotal): array
    {
        return [
            'page'                   => $page,
            'size'                   => $size,
            'includeTotal'           => $includeTotal,
            'strStoreId'             => $f['strStoreId'],
            'schoolYearId'           => $f['schoolYearId'],
            'id'                     => $f['id'],
            'registrationId'         => $f['registrationId'],
            'registrationStatusId'   => $f['registrationStatusId'],
            'subscriptionServiceId'  => $f['subscriptionServiceId'],
            'prestationId'           => $f['prestationId'],
            'prestationTypeId'       => $f['prestationTypeId'],
            'orderId'                => $f['orderId'],
            'studentId'              => $f['studentId'],
            'classId'                => $f['classId'],
            'levelSessionId'         => $f['levelSessionId'],
            'levelSessionPackageId'  => $f['levelSessionPackageId'],
            'levelSessionPackageIds' => $f['levelSessionPackageIds'],
            'serviceTypeIds'         => $f['serviceTypeIds'],
            'dueDateStartDate'       => $f['dueDateStartDate'],
            'dueDateEndDate'         => $f['dueDateEndDate'],
            'startDay'               => $f['startDay'],
            'endDay'                 => $f['endDay'],
        ];
    }
}
