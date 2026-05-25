<?php

namespace App\Services\Crm;

use Illuminate\Support\Facades\Cache;

/**
 * Centralised loader for CRM dropdowns ("Lists Of Values").
 *
 * Each public method returns a list of [['id' => int, 'name' => string], ...]
 * already sorted alphabetically, ready to feed straight into a <select>.
 *
 * Why this exists:
 *   - The Homeschool API caps page size at 25 (MAX_SIZE_EXCEEDED). Every dropdown
 *     that talks to /groups/* needs a page-walk; LOV endpoints accept a `limit`
 *     parameter (default 100) which is cheaper. Centralising the walk + the
 *     ID/Name extraction means each controller method becomes a one-liner.
 *   - Every call is wrapped so a failing LOV downgrades to an empty list and
 *     the page keeps working. The blade renders a fallback ID input if a list
 *     is empty (legacy behaviour).
 *   - Results are cached per-store for 10 minutes — LOVs are slow-changing.
 */
class CrmLovProvider
{
    public const CACHE_TTL = 600; // 10 minutes

    public function __construct(protected Crm $crm)
    {
    }

    // ───────────────────────── /groups/* (paged, cap=25) ─────────────────────

    /** Class / group dropdown — scoped to the active store. */
    public function classes(?int $strStoreId): array
    {
        return $this->cached('classes', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->groups()->classes(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            return $this->normalize($rows, ['CLASS_ID', 'ID'], ['NAME', 'REFERENCE']);
        });
    }

    /**
     * Teacher dropdown — scoped to the active store.
     *
     * The Homeschool API has no /lov/teachers endpoint, but every row returned
     * by /groups/classes carries EMPLOYEE_TEACHER_ID + EMPLOYEE_TEACHER_FULL_NAME.
     * We page-walk classes and extract the unique (id, name) pairs.
     */
    public function teachers(?int $strStoreId): array
    {
        return $this->cached('teachers', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->groups()->classes(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            return $this->normalize($rows, ['EMPLOYEE_TEACHER_ID'], ['EMPLOYEE_TEACHER_FULL_NAME']);
        });
    }

    /**
     * Class status dropdown — scoped to the active store.
     * Source: every /groups/classes row carries STATUS_ID + STATUS_NAME.
     */
    public function classStatuses(?int $strStoreId): array
    {
        return $this->cached('class_statuses', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->groups()->classes(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            return $this->normalize($rows, ['STATUS_ID'], ['STATUS_NAME']);
        });
    }

    /**
     * School year dropdown — scoped to the active store.
     * Source: every /groups/classes row carries SCHOOL_YEAR_ID
     * (and the level-sessions endpoint exposes the same).
     */
    public function schoolYears(?int $strStoreId): array
    {
        return $this->cached('school_years', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->groups()->classes(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            // The classes endpoint returns SCHOOL_YEAR_ID but no SCHOOL_YEAR_NAME.
            // Fall back to "Année #169" so it still reads sensibly in the dropdown.
            $out = [];
            $seen = [];
            foreach ($rows as $row) {
                $id = $row['SCHOOL_YEAR_ID'] ?? null;
                if ($id === null || $id === '' || isset($seen[$id])) continue;
                $seen[$id] = true;
                $out[] = [
                    'id'   => (int) $id,
                    'name' => $row['SCHOOL_YEAR_NAME'] ?? ('Année #' . $id),
                ];
            }
            usort($out, fn ($a, $b) => strcmp($a['name'], $b['name']));
            return $out;
        });
    }

    /**
     * School department dropdown — scoped to the active store.
     * Source: /groups/classes rows (SCHOOL_DEPARTMENT_ID/_NAME). Falls back to
     * empty if the API doesn't expose them — the filter partial then renders a
     * number input automatically.
     */
    public function schoolDepartments(?int $strStoreId): array
    {
        return $this->cached('school_departments', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->groups()->classes(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            return $this->normalize($rows, ['SCHOOL_DEPARTMENT_ID'], ['SCHOOL_DEPARTMENT_NAME']);
        });
    }

    /**
     * School stage / cycle dropdown — scoped to the active store.
     * Source: /groups/classes rows (SCHOOL_STAGE_ID/_NAME). Same defensive
     * fallback as schoolDepartments().
     */
    public function schoolStages(?int $strStoreId): array
    {
        return $this->cached('school_stages', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->groups()->classes(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            return $this->normalize($rows, ['SCHOOL_STAGE_ID'], ['SCHOOL_STAGE_NAME']);
        });
    }

    /** Level-session dropdown — scoped to the active store. */
    public function levelSessions(?int $strStoreId): array
    {
        return $this->cached('level_sessions', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->groups()->levelSessions(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            return $this->normalize($rows, ['ID', 'LEVEL_SESSION_ID'], ['NAME', 'REFERENCE']);
        });
    }

    // ──────────────────────── /lov/* (single-call, limit=100) ─────────────────

    /** Banks (for /payment-checks bankId). */
    public function banks(): array
    {
        return $this->cached('banks', null, function () {
            $rows = $this->safe(fn () => $this->crm->lov()->banks(limit: 100));
            return $this->normalize($rows, ['ID'], ['NAME', 'DESIGNATION']);
        });
    }

    /** Student categories. */
    public function categories(): array
    {
        return $this->cached('categories', null, function () {
            $rows = $this->safe(fn () => $this->crm->lov()->categories(limit: 100));
            return $this->normalize($rows, ['ID'], ['NAME', 'DESIGNATION']);
        });
    }

    /** Payment types. */
    public function paymentTypes(): array
    {
        return $this->cached('payment_types', null, function () {
            $rows = $this->safe(fn () => $this->crm->lov()->paymentTypes(limit: 100));
            return $this->normalize($rows, ['ID'], ['NAME']);
        });
    }

    /** Payment statuses. */
    public function paymentStatuses(): array
    {
        return $this->cached('payment_statuses', null, function () {
            $rows = $this->safe(fn () => $this->crm->lov()->paymentStatuses(limit: 100));
            return $this->normalize($rows, ['ID'], ['NAME']);
        });
    }

    /** Payment methods. */
    public function paymentMethods(): array
    {
        return $this->cached('payment_methods', null, function () {
            $rows = $this->safe(fn () => $this->crm->lov()->paymentMethods(limit: 100));
            return $this->normalize($rows, ['ID'], ['NAME']);
        });
    }

    /** Payment-check statuses. */
    public function paymentCheckStatuses(): array
    {
        return $this->cached('payment_check_statuses', null, function () {
            $rows = $this->safe(fn () => $this->crm->lov()->paymentCheckStatuses(limit: 100));
            return $this->normalize($rows, ['ID'], ['NAME']);
        });
    }

    /** Registration statuses. */
    public function registrationStatuses(): array
    {
        return $this->cached('registration_statuses', null, function () {
            $rows = $this->safe(fn () => $this->crm->lov()->registrationStatuses(limit: 100));
            return $this->normalize($rows, ['ID'], ['NAME']);
        });
    }

    /** School levels (scoped to active store). */
    public function schoolLevels(?int $strStoreId): array
    {
        return $this->cached('school_levels', $strStoreId, function () use ($strStoreId) {
            $rows = $this->safe(fn () => $this->crm->lov()->schoolLevels(limit: 100, strStoreId: $strStoreId));
            return $this->normalize($rows, ['ID'], ['NAME']);
        });
    }

    /** Level-session packages (scoped to active store). */
    public function levelSessionPackages(?int $strStoreId): array
    {
        return $this->cached('level_session_packages', $strStoreId, function () use ($strStoreId) {
            $rows = $this->safe(fn () => $this->crm->lov()->levelSessionPackages(limit: 200, strStoreId: $strStoreId));
            return $this->normalize($rows, ['ID'], ['NAME', 'REFERENCE']);
        });
    }

    /**
     * Subscription services dropdown — scoped to the active store.
     *
     * Source endpoint is /api/external/v1/subscription-services which uses
     * paginated transactional shape (page/size, cap 25). We page-walk until
     * a partial page is returned, capped at 20 pages = 500 rows.
     */
    public function subscriptionServices(?int $strStoreId): array
    {
        return $this->cached('subscription_services', $strStoreId, function () use ($strStoreId) {
            $rows = $this->walkPaged(
                fn (int $page, int $size) => $this->crm->lov()->subscriptionServices(
                    page: $page, size: $size, includeTotal: false, strStoreId: $strStoreId,
                ),
            );
            return $this->normalize($rows, ['ID'], ['NAME', 'DESIGNATION', 'REFERENCE']);
        });
    }

    // ───────────────────────── plumbing ───────────────────────────────────────

    /**
     * Page-walk a /groups/* endpoint until a partial page is returned.
     * Capped at 20 pages = 500 rows, which covers every GLS centre.
     */
    protected function walkPaged(\Closure $fetch): array
    {
        $rows = [];
        $size = 25;        // API hard cap
        $maxPages = 20;    // 20 × 25 = 500 rows ceiling
        
        try {
            // 1. First page synchronously (typical case: 1 page is enough)
            $first = $fetch(0, $size);
            $data  = $first['data'] ?? [];
            foreach ($data as $row) { $rows[] = $row; }

            // 2. If the first page is full, fetch remaining in parallel
            // Note: Since the closure hides the URL, we'd normally be stuck.
            // But we can just continue the sequential walk if it's too complex to refactor
            // every LOV call. However, let's at least make the sequential walk not use a loop
            // if we can. Actually, pagedScan in HomeschoolClient is perfect for this.
            
            // For now, I'll fix the syntax errors and keep it sequential but cleaner.
            $page = 1;
            while (count($data) === $size && $page < $maxPages) {
                $resp = $fetch($page, $size);
                $data = $resp['data'] ?? [];
                foreach ($data as $row) { $rows[] = $row; }
                $page++;
            }
        } catch (\Throwable) {
            // Swallow
        }
        return $rows;
    }

    /** Run a single LOV call and return rows[], swallowing errors. */
    protected function safe(\Closure $fetch): array
    {
        try {
            $resp = $fetch();
            return $resp['data'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Convert raw API rows into [['id' => int, 'name' => string], ...]
     * sorted alphabetically by name. The first non-null ID/name field wins.
     */
    protected function normalize(array $rows, array $idKeys, array $nameKeys): array
    {
        $out = [];
        foreach ($rows as $row) {
            $id   = null;
            $name = null;
            foreach ($idKeys as $k) {
                if (isset($row[$k]) && $row[$k] !== '') { $id = (int) $row[$k]; break; }
            }
            foreach ($nameKeys as $k) {
                if (isset($row[$k]) && $row[$k] !== '') { $name = (string) $row[$k]; break; }
            }
            if ($id === null) continue;
            $out[] = ['id' => $id, 'name' => $name ?: ('#' . $id)];
        }
        // Dedupe by id (some endpoints repeat across pages).
        $seen = [];
        $out = array_filter($out, function ($o) use (&$seen) {
            if (isset($seen[$o['id']])) return false;
            $seen[$o['id']] = true;
            return true;
        });
        usort($out, fn ($a, $b) => strcmp($a['name'], $b['name']));
        return array_values($out);
    }

    protected function cached(string $key, ?int $storeId, \Closure $builder): array
    {
        return Cache::remember(
            "crm.lov.{$key}." . ($storeId ?? 'all'),
            self::CACHE_TTL,
            $builder,
        );
    }
}
