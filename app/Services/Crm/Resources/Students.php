<?php

namespace App\Services\Crm\Resources;

class Students extends Resource
{
    /**
     * GET /api/external/v1/students — List students
     *
     * Required scope: students:read.
     * Pagination is mandatory (page, size).
     *
     * Filters (all optional):
     *   strStoreId          — store identifier (validated against token store permissions)
     *   reference           — student reference
     *   firstName           — first name filter
     *   lastName            — last name filter
     *   phoneNumber         — phone number filter
     *   sexe                — gender/sex value
     *   categoryId          — category identifier
     *   registrationStatus  — registration status filter
     *
     * @return array{
     *   success: bool,
     *   data: array<int, array<string,mixed>>,
     *   pagination: array{page:int,size:int,returned?:int,hasNext?:bool,hasMore?:bool,totalElements?:int,totalPages?:int},
     *   meta: array{apiVersion:string,requestId:string,timestamp?:string}
     * }
     */
    public function list(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?string $reference = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phoneNumber = null,
        ?string $sexe = null,
        ?int $categoryId = null,
        ?string $registrationStatus = null,
    ): array {
        return $this->client->get('/api/external/v1/students', array_filter([
            'page'               => $page,
            'size'               => $size,
            'includeTotal'       => $includeTotal,
            'strStoreId'         => $strStoreId,
            'reference'          => $reference,
            'firstName'          => $firstName,
            'lastName'           => $lastName,
            'phoneNumber'        => $phoneNumber,
            'sexe'               => $sexe,
            'categoryId'         => $categoryId,
            'registrationStatus' => $registrationStatus,
        ], fn ($v) => $v !== null));
    }

    /**
     * GET /api/external/v1/lov/students — Search students LOV (paginated lookup)
     *
     * Required scope: students:read.
     * Despite being under /lov/, this endpoint requires page/size like a transactional one.
     */
    public function search(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
    ): array {
        return $this->client->get('/api/external/v1/lov/students', [
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal,
        ]);
    }

    /**
     * GET /api/external/v1/session-presence — List student session presence
     *
     * Required scope: session-presence:read.
     *
     * Response rows include: STUDENT_ID, FIRST_NAME, LAST_NAME, SESSION_ID,
     * SESSION_DATE, PRESENCE ("Y"/"N"), ABSENCE ("Y"/"N"), PRESENCE_STATUS (1=present, 0=absent).
     *
     * Filters (all optional):
     *   strStoreId       — store identifier
     *   schoolYearId     — school year filter
     *   date             — ISO yyyy-MM-dd session date
     *   presence         — "Y" to filter present students
     *   absence          — "Y" to filter absent students
     *   presenceStatus   — 1 = present, 0 = absent (computed)
     *   classId          — class/group identifier
     *   levelSessionId   — level session identifier
     *   sessionId        — course/session identifier
     *   registrationId   — registration identifier
     *   studentId        — student identifier
     */
    public function sessionPresence(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?string $date = null,
        ?string $presence = null,
        ?string $absence = null,
        ?int $presenceStatus = null,
        ?int $classId = null,
        ?int $levelSessionId = null,
        ?int $sessionId = null,
        ?int $registrationId = null,
        ?int $studentId = null,
    ): array {
        return $this->client->get('/api/external/v1/session-presence', array_filter([
            'page'           => $page,
            'size'           => $size,
            'includeTotal'   => $includeTotal,
            'strStoreId'     => $strStoreId,
            'schoolYearId'   => $schoolYearId,
            'date'           => $date,
            'presence'       => $presence,
            'absence'        => $absence,
            'presenceStatus' => $presenceStatus,
            'classId'        => $classId,
            'levelSessionId' => $levelSessionId,
            'sessionId'      => $sessionId,
            'registrationId' => $registrationId,
            'studentId'      => $studentId,
        ], fn ($v) => $v !== null));
    }
}
