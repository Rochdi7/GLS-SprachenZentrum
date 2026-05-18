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
}
