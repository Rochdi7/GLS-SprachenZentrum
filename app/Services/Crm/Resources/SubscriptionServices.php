<?php

namespace App\Services\Crm\Resources;

class SubscriptionServices extends Resource
{
    /**
     * GET /api/external/v1/subscription-services — List subscription services
     *
     * Required scope: subscription-services:read. Tenant/module resolved from
     * the token. Pagination is mandatory; size is capped at 25.
     *
     * Response rows include: ID, REFERENCE, STUDENT_ID, REGISTRATION_ID,
     * TOTAL_PRICE, REST_AMOUNT, DUE_DATE, SERVICE_TYPE_NAME.
     *
     * Filters (all optional):
     *   strStoreId                     — store identifier
     *   schoolYearId                   — school year filter (SCL_SERVICE.SCHOOL_YEAR_ID)
     *   id                             — subscription service identifier
     *   studentId                      — student identifier
     *   registrationId                 — registration identifier
     *   dueDate                        — ISO yyyy-MM-dd, returns services with due date ≤ this
     *   levelSessionPackageId          — level session package identifier
     *   subscriptionServiceStatusId    — subscription service status identifier
     *   levelSessionId                 — level session identifier
     */
    public function list(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $schoolYearId = null,
        ?int $id = null,
        ?int $studentId = null,
        ?int $registrationId = null,
        ?string $dueDate = null,
        ?int $levelSessionPackageId = null,
        ?int $subscriptionServiceStatusId = null,
        ?int $levelSessionId = null,
    ): array {
        return $this->client->get('/api/external/v1/subscription-services', array_filter([
            'page'                        => $page,
            'size'                        => $size,
            'includeTotal'                => $includeTotal,
            'strStoreId'                  => $strStoreId,
            'schoolYearId'                => $schoolYearId,
            'id'                          => $id,
            'studentId'                   => $studentId,
            'registrationId'              => $registrationId,
            'dueDate'                     => $dueDate,
            'levelSessionPackageId'       => $levelSessionPackageId,
            'subscriptionServiceStatusId' => $subscriptionServiceStatusId,
            'levelSessionId'              => $levelSessionId,
        ], fn ($v) => $v !== null));
    }
}
