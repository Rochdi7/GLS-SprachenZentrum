<?php

namespace App\Services\Crm\Resources;

class EmployeeSalaries extends Resource
{
    /**
     * GET /api/external/v1/employee-calculated-salary-classes
     * List employee calculated salary class records.
     *
     * Required scope: employee-salaries:read. Tenant/module resolved from the
     * token. Pagination is mandatory; size is capped at 25.
     *
     * Response rows include: PK, EMPLOYEE_ID, FIRST_NAME, LAST_NAME, CLASS_ID,
     * OPERATION_YEAR, OPERATION_MONTH, MONTHLY_SALARY, REMAINING_AMOUNT.
     *
     * Filters (all optional):
     *   strStoreId        — store identifier
     *   employeeId        — employee identifier
     *   classId           — class / group identifier
     *   operationMonth    — 1..12
     *   operationYear     — e.g. 2026
     */
    public function calculatedSalaryClasses(
        int $page = 0,
        int $size = 10,
        bool $includeTotal = true,
        ?int $strStoreId = null,
        ?int $employeeId = null,
        ?int $classId = null,
        ?int $operationMonth = null,
        ?int $operationYear = null,
    ): array {
        return $this->client->get('/api/external/v1/employee-calculated-salary-classes', array_filter([
            'page'           => $page,
            'size'           => $size,
            'includeTotal'   => $includeTotal,
            'strStoreId'     => $strStoreId,
            'employeeId'     => $employeeId,
            'classId'        => $classId,
            'operationMonth' => $operationMonth,
            'operationYear'  => $operationYear,
        ], fn ($v) => $v !== null));
    }
}
