<?php

namespace App\Services\Crm\Resources;

/**
 * LOV (List Of Values) reference endpoints.
 *
 * These return small, slow-changing lookup data. Required scope: lov:read.
 * Server enforces a safety `limit` (default 100) — there is no page/size here.
 *
 * Note: /api/external/v1/lov/students is NOT here because it actually requires
 * page/size — it lives on the Students resource as Students::search().
 */
class Lov extends Resource
{
    /**
     * GET /api/external/v1/lov/school-levels
     *
     * Filters:
     *   limit                — safety cap (default 100)
     *   strStoreId           — optional store identifier
     *   schoolDepartmentId   — department filter
     *   schoolStageId        — stage filter
     *   active               — active flag
     */
    public function schoolLevels(
        int $limit = 100,
        ?int $strStoreId = null,
        ?int $schoolDepartmentId = null,
        ?int $schoolStageId = null,
        ?string $active = null,
    ): array {
        return $this->client->get('/api/external/v1/lov/school-levels', array_filter([
            'limit'              => $limit,
            'strStoreId'         => $strStoreId,
            'schoolDepartmentId' => $schoolDepartmentId,
            'schoolStageId'      => $schoolStageId,
            'active'             => $active,
        ], fn ($v) => $v !== null));
    }

    /** GET /api/external/v1/lov/registration-statuses */
    public function registrationStatuses(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/registration-statuses', [
            'limit' => $limit,
        ]);
    }

    /**
     * GET /api/external/v1/lov/registration-conventions
     *
     * Filters:
     *   limit        — safety cap (default 100)
     *   strStoreId   — optional store identifier
     */
    public function registrationConventions(int $limit = 100, ?int $strStoreId = null): array
    {
        return $this->client->get('/api/external/v1/lov/registration-conventions', array_filter([
            'limit'      => $limit,
            'strStoreId' => $strStoreId,
        ], fn ($v) => $v !== null));
    }

    /** GET /api/external/v1/lov/registration-change-status-reasons */
    public function registrationChangeStatusReasons(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/registration-change-status-reasons', [
            'limit' => $limit,
        ]);
    }

    /** GET /api/external/v1/lov/payment-types */
    public function paymentTypes(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/payment-types', [
            'limit' => $limit,
        ]);
    }

    /** GET /api/external/v1/lov/payment-statuses */
    public function paymentStatuses(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/payment-statuses', [
            'limit' => $limit,
        ]);
    }

    /** GET /api/external/v1/lov/payment-methods */
    public function paymentMethods(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/payment-methods', [
            'limit' => $limit,
        ]);
    }

    /** GET /api/external/v1/lov/payment-check-statuses */
    public function paymentCheckStatuses(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/payment-check-statuses', [
            'limit' => $limit,
        ]);
    }

    /**
     * GET /api/external/v1/lov/level-session-packages
     *
     * Filters:
     *   limit            — safety cap (default 100)
     *   strStoreId       — optional store identifier
     *   levelSessionId   — restrict packages to a single level session
     */
    public function levelSessionPackages(
        int $limit = 100,
        ?int $strStoreId = null,
        ?int $levelSessionId = null,
    ): array {
        return $this->client->get('/api/external/v1/lov/level-session-packages', array_filter([
            'limit'          => $limit,
            'strStoreId'     => $strStoreId,
            'levelSessionId' => $levelSessionId,
        ], fn ($v) => $v !== null));
    }

    /** GET /api/external/v1/lov/categories */
    public function categories(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/categories', [
            'limit' => $limit,
        ]);
    }

    /** GET /api/external/v1/lov/expense-types */
    public function expenseTypes(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/expense-types', [
            'limit' => $limit,
        ]);
    }

    /**
     * GET /api/external/v1/lov/banks
     *
     * Note: full spec was truncated in the API docs the user pasted — only the
     * standard `limit` is wired. If the live endpoint accepts a `strStoreId`,
     * add it here as an optional parameter.
     */
    public function banks(int $limit = 100): array
    {
        return $this->client->get('/api/external/v1/lov/banks', [
            'limit' => $limit,
        ]);
    }

    /**
     * GET /api/external/v1/subscription-services — List subscription services
     *
     * Required scope: lov:read (treated as reference data — the published spec
     * shows no parameter schema beyond pagination, so this is wired with the
     * standard page/size/includeTotal triple plus an optional strStoreId.)
     *
     * Filters (all optional):
     *   strStoreId    — store identifier (validated against token scope when provided)
     */
    public function subscriptionServices(
        int $page = 0,
        int $size = 25,
        bool $includeTotal = false,
        ?int $strStoreId = null,
    ): array {
        return $this->client->get('/api/external/v1/subscription-services', array_filter([
            'page'         => $page,
            'size'         => $size,
            'includeTotal' => $includeTotal,
            'strStoreId'   => $strStoreId,
        ], fn ($v) => $v !== null));
    }
}
