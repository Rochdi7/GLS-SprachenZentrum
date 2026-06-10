<?php

namespace App\Services\Crm\Resources;

class Employees extends Resource
{
    /**
     * GET /api/external/v1/employees — List employees (paginated)
     *
     * Required scope: employees:read.
     *
     * Filters (all optional):
     *   strStoreId  — store identifier
     *   page        — 0-based page number
     *   size        — page size (max 500)
     *   includeTotal — include pagination total count
     */
    public function list(
        int $page = 0,
        int $size = 100,
        bool $includeTotal = true,
        ?int $strStoreId = null,
    ): array {
        return $this->client->get('/api/external/v1/employees', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal ? 'true' : 'false',
            'strStoreId'   => $strStoreId,
        ], fn ($v) => $v !== null));
    }
}
