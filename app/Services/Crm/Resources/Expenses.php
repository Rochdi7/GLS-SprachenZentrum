<?php

namespace App\Services\Crm\Resources;

class Expenses extends Resource
{
    /**
     * GET /api/external/v1/expenses — List expenses (paginated, maxPageSize=25)
     *
     * Required scope: expenses:read.
     * Tenant/module resolved from token. strStoreId optional for multi-store.
     *
     * Filters (all optional):
     *   strStoreId      — store identifier
     *   schoolYearId    — school year filter
     *   id              — specific expense id
     *   expenseStatusId — status filter
     *   expenseTypeId   — type filter (use lov/expense-types for IDs)
     *   cashBoxId       — cash box filter
     *   levelSessionId  — level session filter
     *   paymentMethodId — payment method filter
     *   reference       — reference LIKE contains
     *   invoiceReference — invoice reference LIKE contains
     *   startDate       — ISO yyyy-MM-dd
     *   endDate         — ISO yyyy-MM-dd
     */
    public function list(
        int $page = 0,
        int $size = 25,
        bool $includeTotal = false,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $id = null,
        ?int $expenseStatusId = null,
        ?int $expenseTypeId = null,
        ?int $cashBoxId = null,
        ?int $levelSessionId = null,
        ?int $paymentMethodId = null,
        ?string $reference = null,
        ?string $invoiceReference = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return $this->client->get('/api/external/v1/expenses', array_filter([
            'page'             => $page,
            'size'             => min($size, 25),
            'includeTotal'     => $includeTotal ? 'true' : 'false',
            'strStoreId'       => $strStoreId,
            'schoolYearId'     => $schoolYearId,
            'id'               => $id,
            'expenseStatusId'  => $expenseStatusId,
            'expenseTypeId'    => $expenseTypeId,
            'cashBoxId'        => $cashBoxId,
            'levelSessionId'   => $levelSessionId,
            'paymentMethodId'  => $paymentMethodId,
            'reference'        => $reference,
            'invoiceReference' => $invoiceReference,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/expenses — Bulk list expenses (size up to 500)
     *
     * Same SQL, filters, security, scopes and response model as list(), but the
     * page size cap is 500 instead of 25. Use this for syncs/exports — 20× fewer
     * requests. Required scope: expenses:read.
     */
    public function bulkList(
        int $page = 0,
        int $size = 500,
        bool $includeTotal = false,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $id = null,
        ?int $expenseStatusId = null,
        ?int $expenseTypeId = null,
        ?int $cashBoxId = null,
        ?int $levelSessionId = null,
        ?int $paymentMethodId = null,
        ?string $reference = null,
        ?string $invoiceReference = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return $this->client->get('/api/external/v1/bulk/expenses', array_filter([
            'page'             => $page,
            'size'             => min($size, 500),
            'includeTotal'     => $includeTotal ? 'true' : 'false',
            'strStoreId'       => $strStoreId,
            'schoolYearId'     => $schoolYearId,
            'id'               => $id,
            'expenseStatusId'  => $expenseStatusId,
            'expenseTypeId'    => $expenseTypeId,
            'cashBoxId'        => $cashBoxId,
            'levelSessionId'   => $levelSessionId,
            'paymentMethodId'  => $paymentMethodId,
            'reference'        => $reference,
            'invoiceReference' => $invoiceReference,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
        ], fn ($v) => $v !== null));
    }
}
