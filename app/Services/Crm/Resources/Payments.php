<?php

namespace App\Services\Crm\Resources;

class Payments extends Resource
{
    /**
     * GET /api/external/v1/payments — List payments
     *
     * Required scope: payments:read.
     *
     * Filters (all optional):
     *   strStoreId         — store identifier
     *   schoolYearId       — school year filter
     *   reference          — payment reference
     *   studentId          — student identifier
     *   paymentTypeId      — payment type id
     *   paymentStatusId    — payment status id
     *   paymentMethodeId   — payment method id (NOTE: API spelling keeps the typo "Methode")
     *   startDate          — ISO yyyy-MM-dd
     *   endDate            — ISO yyyy-MM-dd
     */
    public function list(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?string $reference = null,
        ?int $studentId = null,
        ?int $paymentTypeId = null,
        ?int $paymentStatusId = null,
        ?int $paymentMethodeId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return $this->client->get('/api/external/v1/payments', array_filter([
            'page'             => $page,
            'size'             => $size,
            'includeTotal'     => $includeTotal,
            'strStoreId'       => $strStoreId,
            'schoolYearId'     => $schoolYearId,
            'reference'        => $reference,
            'studentId'        => $studentId,
            'paymentTypeId'    => $paymentTypeId,
            'paymentStatusId'  => $paymentStatusId,
            'paymentMethodeId' => $paymentMethodeId,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/payment-collection — Bulk list payment collection (size up to 500)
     *
     * Required scope: payments:read. Same filters as collection().
     */
    public function bulkCollection(
        int $page = 0,
        int $size = 500,
        bool $includeTotal = false,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $registrationId = null,
        ?int $registrationStatusId = null,
        ?string $serviceTypeIds = null,
        ?string $levelSessionPackageIds = null,
        ?string $dueDateStartDate = null,
        ?string $dueDateEndDate = null,
        ?int $startDay = null,
        ?int $endDay = null,
    ): array {
        return $this->client->get('/api/external/v1/bulk/payment-collection', array_filter([
            'page'                   => $page,
            'size'                   => $size,
            'includeTotal'           => $includeTotal,
            'strStoreId'             => $strStoreId,
            'schoolYearId'           => $schoolYearId,
            'registrationId'         => $registrationId,
            'registrationStatusId'   => $registrationStatusId,
            'serviceTypeIds'         => $serviceTypeIds,
            'levelSessionPackageIds' => $levelSessionPackageIds,
            'dueDateStartDate'       => $dueDateStartDate,
            'dueDateEndDate'         => $dueDateEndDate,
            'startDay'               => $startDay,
            'endDay'                 => $endDay,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/payments — Bulk list payments (size up to 500)
     *
     * Required scope: payments:read. Same filters as list().
     */
    public function bulkList(
        int $page = 0,
        int $size = 500,
        bool $includeTotal = false,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?string $reference = null,
        ?int $studentId = null,
        ?int $paymentTypeId = null,
        ?int $paymentStatusId = null,
        ?int $paymentMethodId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $cashBoxId = null,
    ): array {
        return $this->client->get('/api/external/v1/bulk/payments', array_filter([
            'page'            => $page,
            'size'            => $size,
            'includeTotal'    => $includeTotal,
            'strStoreId'      => $strStoreId,
            'schoolYearId'    => $schoolYearId,
            'reference'       => $reference,
            'studentId'       => $studentId,
            'paymentTypeId'   => $paymentTypeId,
            'paymentStatusId' => $paymentStatusId,
            'paymentMethodId' => $paymentMethodId,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'cashBoxId'       => $cashBoxId,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/payment-allocations — Bulk list payment allocations (size up to 500)
     *
     * Required scope: payments:read. Same filters as allocations().
     */
    public function bulkAllocations(
        int $page = 0,
        int $size = 500,
        bool $includeTotal = false,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $classId = null,
        ?int $levelSessionId = null,
        ?int $registrationId = null,
        ?int $studentId = null,
        ?string $effectiveDatePaymentAllocation = null,
        ?int $cashBoxId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return $this->client->get('/api/external/v1/bulk/payment-allocations', array_filter([
            'page'                           => $page,
            'size'                           => $size,
            'includeTotal'                   => $includeTotal,
            'strStoreId'                     => $strStoreId,
            'schoolYearId'                   => $schoolYearId,
            'classId'                        => $classId,
            'levelSessionId'                 => $levelSessionId,
            'registrationId'                 => $registrationId,
            'studentId'                      => $studentId,
            'effectiveDatePaymentAllocation' => $effectiveDatePaymentAllocation,
            'cashBoxId'                      => $cashBoxId,
            'startDate'                      => $startDate,
            'endDate'                        => $endDate,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/payment-checks — List payment checks
     *
     * Required scope: payment-checks:read.
     *
     * Filters (all optional):
     *   strStoreId             — store identifier
     *   schoolYearId           — school year filter
     *   checkNumber            — check number
     *   studentId              — student identifier
     *   paymentCheckStatusId   — payment check status id
     *   bankId                 — bank id
     *   dueDateMin             — ISO yyyy-MM-dd
     *   dueDateMax             — ISO yyyy-MM-dd
     */
    public function checks(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?string $checkNumber = null,
        ?int $studentId = null,
        ?int $paymentCheckStatusId = null,
        ?int $bankId = null,
        ?string $dueDateMin = null,
        ?string $dueDateMax = null,
    ): array {
        return $this->client->get('/api/external/v1/payment-checks', array_filter([
            'page'                 => $page,
            'size'                 => $size,
            'includeTotal'         => $includeTotal,
            'strStoreId'           => $strStoreId,
            'schoolYearId'         => $schoolYearId,
            'checkNumber'          => $checkNumber,
            'studentId'            => $studentId,
            'paymentCheckStatusId' => $paymentCheckStatusId,
            'bankId'               => $bankId,
            'dueDateMin'           => $dueDateMin,
            'dueDateMax'           => $dueDateMax,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/payment-allocations — List payment allocations
     *
     * Required scope: payments:read.
     *
     * Response rows include: ID, AMOUNT, PAYMENT_ID, PAYMENT_REFERENCE,
     * STUDENT_ID, STUDENT_FULL_NAME, REGISTRATION_ID, SERVICE_TYPE_NAME,
     * EFFECTIVE_DATE_PAYMENT, EFFECTIVE_DATE_PAYMENT_ALLOCATION.
     *
     * Filters (all optional):
     *   strStoreId                       — store identifier
     *   schoolYearId                     — school year filter
     *   classId                          — class/group identifier
     *   levelSessionId                   — level session identifier
     *   registrationId                   — registration identifier
     *   studentId                        — student identifier
     *   effectiveDatePaymentAllocation   — ISO yyyy-MM-dd
     *   cashBoxId                        — cash box identifier
     *   startDate                        — ISO yyyy-MM-dd (must be paired with endDate)
     *   endDate                          — ISO yyyy-MM-dd (must be paired with startDate)
     */
    public function allocations(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $classId = null,
        ?int $levelSessionId = null,
        ?int $registrationId = null,
        ?int $studentId = null,
        ?string $effectiveDatePaymentAllocation = null,
        ?int $cashBoxId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return $this->client->get('/api/external/v1/payment-allocations', array_filter([
            'page'                           => $page,
            'size'                           => $size,
            'includeTotal'                   => $includeTotal,
            'strStoreId'                     => $strStoreId,
            'schoolYearId'                   => $schoolYearId,
            'classId'                        => $classId,
            'levelSessionId'                 => $levelSessionId,
            'registrationId'                 => $registrationId,
            'studentId'                      => $studentId,
            'effectiveDatePaymentAllocation' => $effectiveDatePaymentAllocation,
            'cashBoxId'                      => $cashBoxId,
            'startDate'                      => $startDate,
            'endDate'                        => $endDate,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/payment-collection — List payment collection / receivables
     *
     * Required scope: payments:read. Tenant/module is resolved from the token.
     * Pagination is mandatory; size is capped at 25 by the backend.
     * includeTotal defaults to false for performance — set true only when
     * totalElements/totalPages are actually needed.
     *
     * Filters (all optional unless noted):
     *   strStoreId                — store identifier (validated against token scope)
     *   schoolYearId              — school year filter
     *   id                        — collection row identifier
     *   registrationId            — registration identifier
     *   registrationStatusId      — registration status identifier
     *   subscriptionServiceId     — subscription service identifier
     *   prestationId              — prestation identifier
     *   prestationTypeId          — prestation type identifier
     *   orderId                   — order identifier
     *   studentId                 — student identifier
     *   classId                   — class / group identifier
     *   levelSessionId            — level session identifier
     *   levelSessionPackageId     — single level session package identifier
     *   levelSessionPackageIds    — comma-separated list, e.g. "10,11,12"
     *   serviceTypeIds            — comma-separated list, e.g. "1,2,3"
     *   dueDateStartDate          — ISO yyyy-MM-dd, start of due-date range
     *   dueDateEndDate            — ISO yyyy-MM-dd, end of due-date range
     *   startDay                  — minimum payment delay days
     *   endDay                    — maximum payment delay days
     */
    public function collection(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = false,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $id = null,
        ?int $registrationId = null,
        ?int $registrationStatusId = null,
        ?int $subscriptionServiceId = null,
        ?int $prestationId = null,
        ?int $prestationTypeId = null,
        ?int $orderId = null,
        ?int $studentId = null,
        ?int $classId = null,
        ?int $levelSessionId = null,
        ?int $levelSessionPackageId = null,
        ?string $levelSessionPackageIds = null,
        ?string $serviceTypeIds = null,
        ?string $dueDateStartDate = null,
        ?string $dueDateEndDate = null,
        ?int $startDay = null,
        ?int $endDay = null,
    ): array {
        return $this->client->get('/api/external/v1/payment-collection', array_filter([
            'page'                   => $page,
            'size'                   => $size,
            'includeTotal'           => $includeTotal,
            'strStoreId'             => $strStoreId,
            'schoolYearId'           => $schoolYearId,
            'id'                     => $id,
            'registrationId'         => $registrationId,
            'registrationStatusId'   => $registrationStatusId,
            'subscriptionServiceId'  => $subscriptionServiceId,
            'prestationId'           => $prestationId,
            'prestationTypeId'       => $prestationTypeId,
            'orderId'                => $orderId,
            'studentId'              => $studentId,
            'classId'                => $classId,
            'levelSessionId'         => $levelSessionId,
            'levelSessionPackageId'  => $levelSessionPackageId,
            'levelSessionPackageIds' => $levelSessionPackageIds,
            'serviceTypeIds'         => $serviceTypeIds,
            'dueDateStartDate'       => $dueDateStartDate,
            'dueDateEndDate'         => $dueDateEndDate,
            'startDay'               => $startDay,
            'endDay'                 => $endDay,
        ], fn ($v) => $v !== null));
    }
}
