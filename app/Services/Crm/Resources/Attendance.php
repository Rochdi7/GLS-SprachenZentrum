<?php

namespace App\Services\Crm\Resources;

/**
 * Bulk attendance/student/registration data from Wimschool External API v1.
 */
class Attendance extends Resource
{
    /**
     * GET /api/external/v1/bulk/session-presence
     */
    public function sessionPresence(
        int $page = 0,
        int $size = 100,
        bool $includeTotal = true,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $classId = null,
        ?int $strStoreId = null,
        array $extra = [],
    ): array {
        return $this->client->get('/api/external/v1/bulk/session-presence', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'classId'      => $classId,
            'strStoreId'   => $strStoreId,
            ...$extra,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/students
     */
    public function students(
        int $page = 0,
        int $size = 100,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        array $extra = [],
    ): array {
        return $this->client->get('/api/external/v1/bulk/students', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal,
            'strStoreId'   => $strStoreId,
            'schoolYearId' => $schoolYearId,
            ...$extra,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/registrations
     */
    public function registrations(
        int $page = 0,
        int $size = 100,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $classId = null,
        ?int $schoolYearId = null,
        array $extra = [],
    ): array {
        return $this->client->get('/api/external/v1/bulk/registrations', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal,
            'strStoreId'   => $strStoreId,
            'classId'      => $classId,
            'schoolYearId' => $schoolYearId,
            ...$extra,
        ], fn ($v) => $v !== null));
    }
}
