<?php

namespace App\Http\Controllers\Backoffice\Crm;

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
        $strStoreId = $this->currentStrStoreId();

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

            // 2) "Montant total" across ALL pages — matches the reference CRM
            //    that shows the global sum, not just the current page. Cap at
            //    50 pages × 25 rows = 1 250 rows to bound runaway broad filters.
            $amountKeys = ['REST_AMOUNT', 'OPEN_AMOUNT', 'REMAINING_AMOUNT', 'AMOUNT_DUE', 'AMOUNT'];
            $maxPages   = 50;
            $pageSize   = 25;

            for ($p = 0; $p < $maxPages; $p++) {
                $scanArgs = $this->collectionArgs($filters, $p, $pageSize, false);
                $pageData = $crm->payments()->collection(...$scanArgs);

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

                if (count($rows) < $pageSize) break;
            }
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
