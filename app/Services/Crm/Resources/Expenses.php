<?php

namespace App\Services\Crm\Resources;

class Expenses extends Resource
{
    /**
     * GET /api/external/v1/expenses — List expenses
     *
     * Filters (all optional):
     *   strStoreId  — store identifier
     *   startDate   — ISO yyyy-MM-dd
     *   endDate     — ISO yyyy-MM-dd
     *   page        — 0-based page number
     *   size        — page size (max 500)
     *   includeTotal — include pagination total count
     */
    public function list(
        int $page = 0,
        int $size = 25,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return $this->client->get('/api/external/v1/expenses', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal ? 'true' : 'false',
            'strStoreId'   => $strStoreId,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
        ], fn ($v) => $v !== null));
    }
}
