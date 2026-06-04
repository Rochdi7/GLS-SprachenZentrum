<?php

namespace App\Services\Crm\Resources;

class Registrations extends Resource
{
    /**
     * GET /api/external/v1/registrations — List registrations
     *
     * Required scope: registrations:read.
     *
     * Filters (all optional):
     *   strStoreId               — store identifier
     *   schoolYearId             — school year filter
     *   reference                — registration reference
     *   studentId                — student identifier
     *   registrationStatusId     — registration status id
     *   levelSessionId           — level session id
     *   levelSessionPackageIds   — comma-separated package identifiers
     *   startDate                — ISO yyyy-MM-dd
     *   endDate                  — ISO yyyy-MM-dd
     *   filterStatus             — open filter status
     */
    public function list(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?string $reference = null,
        ?int $studentId = null,
        ?int $registrationStatusId = null,
        ?int $levelSessionId = null,
        ?string $levelSessionPackageIds = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $filterStatus = null,
    ): array {
        return $this->client->get('/api/external/v1/registrations', array_filter([
            'page'                   => $page,
            'size'                   => $size,
            'includeTotal'           => $includeTotal,
            'strStoreId'             => $strStoreId,
            'schoolYearId'           => $schoolYearId,
            'reference'              => $reference,
            'studentId'              => $studentId,
            'registrationStatusId'   => $registrationStatusId,
            'levelSessionId'         => $levelSessionId,
            'levelSessionPackageIds' => $levelSessionPackageIds,
            'startDate'              => $startDate,
            'endDate'                => $endDate,
            'filterStatus'           => $filterStatus,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/registrations — Bulk list registrations (size up to 500)
     *
     * Required scope: registrations:read. Same filters as list().
     */
    public function bulkList(
        int $page = 0,
        int $size = 500,
        bool $includeTotal = false,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?string $reference = null,
        ?int $studentId = null,
        ?int $registrationStatusId = null,
        ?int $levelSessionId = null,
        ?string $levelSessionPackageIds = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $filterStatus = null,
    ): array {
        return $this->client->get('/api/external/v1/bulk/registrations', array_filter([
            'page'                   => $page,
            'size'                   => $size,
            'includeTotal'           => $includeTotal,
            'strStoreId'             => $strStoreId,
            'schoolYearId'           => $schoolYearId,
            'reference'              => $reference,
            'studentId'              => $studentId,
            'registrationStatusId'   => $registrationStatusId,
            'levelSessionId'         => $levelSessionId,
            'levelSessionPackageIds' => $levelSessionPackageIds,
            'startDate'              => $startDate,
            'endDate'                => $endDate,
            'filterStatus'           => $filterStatus,
        ], fn ($v) => $v !== null));
    }
}
