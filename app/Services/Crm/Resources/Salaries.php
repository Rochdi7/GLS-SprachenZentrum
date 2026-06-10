<?php

namespace App\Services\Crm\Resources;

class Salaries extends Resource
{
    /**
     * GET /api/external/v1/salaries — List salaries (paginated)
     *
     * Required scope: salaries:read.
     *
     * Filters (all optional):
     *   strStoreId  — store identifier
     *   startDate   — ISO yyyy-MM-dd
     *   endDate     — ISO yyyy-MM-dd
     *   employeeId  — employee identifier
     *   page        — 0-based page number
     *   size        — page size (max 500)
     *   includeTotal — include pagination total count
     */
    public function list(
        int $page = 0,
        int $size = 500,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $employeeId = null,
    ): array {
        return $this->client->get('/api/external/v1/salaries', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal ? 'true' : 'false',
            'strStoreId'   => $strStoreId,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'employeeId'   => $employeeId,
        ], fn ($v) => $v !== null));
    }
}
