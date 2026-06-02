<?php

namespace App\Services\Crm\Resources;

use Illuminate\Support\Facades\Log;

/**
 * Groups (level-sessions, classes) data.
 *
 * Both endpoints require pagination (page, size). Required scope: groups:read.
 */
class Groups extends Resource
{
    /**
     * GET /api/external/v1/groups/level-sessions
     *
     * NOTE: The Swagger spec for this endpoint was not fully visible at the
     * time of writing. The shape below mirrors /groups/classes which is the
     * documented sibling. Tighten signatures once the live spec is confirmed.
     */
    public function levelSessions(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        array $extra = [],
    ): array {
        return $this->client->get('/api/external/v1/groups/level-sessions', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal,
            'strStoreId'   => $strStoreId,
            'schoolYearId' => $schoolYearId,
            ...$extra,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/groups/classes — List classes
     *
     * Response rows include: ID, CLASS_ID, REFERENCE, NAME, NAME_AR, ACTIVE,
     * START_DATE, END_DATE, SCHOOL_LEVEL_NAME, EMPLOYEE_TEACHER_FULL_NAME,
     * CLASSIFICATION_NAME, STATUS_NAME, LIST_STUDENT (JSON string),
     * CLASS_COUNT_STUDENTS_ACTIVE, LIST_STUDENT_ACTIVE,
     * CLASS_COUNT_STUDENTS_ARCHIVED, LIST_STUDENT_ARCHIVED,
     * CLASS_COUNT_STUDENTS_CANCLED, LIST_STUDENT_CANCELED,
     * SERVICE_LIST (JSON string), SCHOOL_YEAR_ID, STR_STORE_ID, etc.
     *
     * Filters (all optional):
     *   strStoreId           — store identifier (validated against token)
     *   schoolYearId         — school year filter
     *   schoolDepartmentId   — department filter
     *   schoolStageId        — stage filter
     *   schoolLevelId        — school level filter
     *   employeeTeacherId    — restrict to one teacher's classes
     *   statusId             — class status filter
     *   history              — "Y" to include historical groups, "N" for active only (default "N")
     */
    public function classes(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $schoolDepartmentId = null,
        ?int $schoolStageId = null,
        ?int $schoolLevelId = null,
        ?int $employeeTeacherId = null,
        ?int $statusId = null,
        ?string $history = null,
    ): array {
        $query = array_filter([
            'page'               => (string) $page,
            'size'               => (string) $size,
            'strStoreId'         => $strStoreId !== null ? (string) $strStoreId : null,
            'schoolYearId'       => $schoolYearId !== null ? (string) $schoolYearId : null,
            'schoolDepartmentId' => $schoolDepartmentId !== null ? (string) $schoolDepartmentId : null,
            'schoolStageId'      => $schoolStageId !== null ? (string) $schoolStageId : null,
            'schoolLevelId'      => $schoolLevelId !== null ? (string) $schoolLevelId : null,
            'employeeTeacherId'  => $employeeTeacherId !== null ? (string) $employeeTeacherId : null,
            'statusId'           => $statusId !== null ? (string) $statusId : null,
            'history'            => $history,
        ], fn ($v) => $v !== null);

        Log::info('Groups::classes query: ' . json_encode($query));

        return $this->client->get('/api/external/v1/groups/classes', $query);
    }

    /**
     * GET /api/external/v1/bulk/groups/level-sessions
     */
    public function bulkLevelSessions(
        int $page = 0,
        int $size = 100,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        array $extra = [],
    ): array {
        return $this->client->get('/api/external/v1/bulk/groups/level-sessions', array_filter([
            'page'         => $page,
            'size'         => $size,
            'strStoreId'   => $strStoreId,
            'schoolYearId' => $schoolYearId,
            ...$extra,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/bulk/groups/classes
     */
    public function bulkClasses(
        int $page = 0,
        int $size = 100,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        array $extra = [],
    ): array {
        return $this->client->get('/api/external/v1/bulk/groups/classes', array_filter([
            'page'         => $page,
            'size'         => $size,
            'strStoreId'   => $strStoreId,
            'schoolYearId' => $schoolYearId,
            ...$extra,
        ], fn ($v) => $v !== null));
    }
}
