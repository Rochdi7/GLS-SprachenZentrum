<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Student-facing CRM pages: students directory, session presence matrix,
 * and registrations list.
 */
class CrmStudentsController extends BaseCrmController
{
    public function students(Request $r): View
    {
        return $this->render(
            'backoffice.crm.students',
            fn (?int $strStoreId) => $this->scopedCrm()->students()->list(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $strStoreId,
                reference: $r->query('reference') ?: null,
                firstName: $r->query('firstName') ?: null,
                lastName:  $r->query('lastName')  ?: null,
                phoneNumber: $r->query('phoneNumber') ?: null,
                categoryId: $r->filled('categoryId') ? (int) $r->query('categoryId') : null,
                registrationStatus: $r->query('registrationStatus') ?: null,
            ),
            extra: [
                'lovCategories' => $this->lovs->categories(),
            ],
        );
    }

    public function sessionPresence(Request $r): View
    {
        $classId    = $r->filled('classId') ? (int) $r->query('classId') : null;
        $strStoreId = $this->currentStrStoreId();
        $crm        = $this->scopedCrm();

        // The Homeschool API only supports a single `date` filter on
        // /session-presence (no range). We expose Date début / Date fin in the
        // UI and apply them client-side after the page-walk. Default to the
        // current month so the matrix isn't blank on first load.
        $startDate = $r->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate   = $r->query('endDate')   ?: now()->endOfMonth()->toDateString();

        $shared = [
            'strStoreId'   => $strStoreId,
            'schoolYearId' => $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
            'date'         => $r->query('date') ?: null,
            'presence'     => $r->query('presence') ?: null,
            'absence'      => $r->query('absence') ?: null,
            'classId'      => $classId,
            'studentId'    => $r->filled('studentId') ? (int) $r->query('studentId') : null,
        ];

        $payload = null;
        $error   = null;

        try {
            $students = $crm->students();

            if ($classId === null) {
                // Without classId the rows can't be sliced by group. Return
                // one page so the view can show an "instructions" empty state.
                $payload = $students->sessionPresence(...[
                    'page' => (int) $r->query('page', 0),
                    'size' => (int) $r->query('size', 20),
                    ...$shared,
                ]);
            } else {
                $shouldFetchByDay = $startDate && $endDate;

                $baseQuery = array_filter([
                    'strStoreId'   => $shared['strStoreId'],
                    'schoolYearId' => $shared['schoolYearId'],
                    'presence'     => $shared['presence'],
                    'absence'      => $shared['absence'],
                    'classId'      => $shared['classId'],
                    'studentId'    => $shared['studentId'],
                ], fn ($v) => $v !== null);

                if ($shouldFetchByDay) {
                    $allRows = $this->fetchPresenceByDay(
                        crm: $crm,
                        startDate: $startDate,
                        endDate: $endDate,
                        baseQuery: $baseQuery,
                    );
                    $rowsBefore = count($allRows);
                } else {
                    $allRows = $crm->client()->pagedScan(
                        path: '/api/external/v1/session-presence',
                        baseQuery: $baseQuery + array_filter(['date' => $shared['date']]),
                        pageSize: 25,
                        maxPages: 80,
                        concurrency: 3,
                    );
                    $rowsBefore = count($allRows);
                }

                $payload = [
                    'success'    => true,
                    'data'       => $allRows,
                    'pagination' => null,
                    'meta'       => [
                        'aggregated' => true,
                        'totalRows'  => count($allRows),
                        'rowsBefore' => $rowsBefore,
                        'strategy'   => $shouldFetchByDay ? 'per-day-parallel' : 'paged-scan',
                    ],
                ];
            }
        } catch (CrmException $e) {
            $error = $e;
        }

        return $this->view('backoffice.crm.session-presence', [
            'payload'        => $payload,
            'error'          => $error,
            'filters'        => $r->query(),
            'classOptions'   => $this->lovs->classes($strStoreId),
            'lovSchoolYears' => $this->lovs->schoolYears($strStoreId),
            'startDate'      => $startDate,
            'endDate'        => $endDate,
        ]);
    }

    public function registrations(Request $r): View
    {
        $strStoreId = $this->currentStrStoreId();

        return $this->render(
            'backoffice.crm.registrations',
            fn (?int $sid) => $this->scopedCrm()->registrations()->list(
                page: (int) $r->query('page', 0),
                size: (int) $r->query('size', 20),
                strStoreId: $sid,
                schoolYearId: $r->filled('schoolYearId') ? (int) $r->query('schoolYearId') : null,
                reference: $r->query('reference') ?: null,
                studentId: $r->filled('studentId') ? (int) $r->query('studentId') : null,
                registrationStatusId: $r->filled('registrationStatusId') ? (int) $r->query('registrationStatusId') : null,
                levelSessionId: $r->filled('levelSessionId') ? (int) $r->query('levelSessionId') : null,
                startDate: $r->query('startDate') ?: null,
                endDate: $r->query('endDate') ?: null,
            ),
            extra: [
                'lovRegistrationStatus' => $this->lovs->registrationStatuses(),
                'lovLevelSessions'      => $this->lovs->levelSessions($strStoreId),
                'lovSchoolYears'        => $this->lovs->schoolYears($strStoreId),
            ],
        );
    }

    /**
     * Fetch session-presence rows day-by-day in parallel for the given range.
     *
     * The /session-presence endpoint accepts a single `date` (not a range),
     * so we expand the range into one query per day and fire them all via
     * Http::pool(). For a 7-day window: ~1 round-trip instead of pagedScan's
     * 50+ sequential page fetches. Hard-capped at 62 days (≈ 2 months) to
     * avoid pathological cases — beyond that we fall back to pagedScan.
     *
     * @param  array  $baseQuery  Shared filters applied to every per-day call
     * @return array<int, array<string,mixed>>
     */
    protected function fetchPresenceByDay(Crm $crm, string $startDate, string $endDate, array $baseQuery): array
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->startOfDay();
        } catch (\Throwable) {
            return [];
        }
        if ($end->lt($start)) {
            return [];
        }
        if ($start->diffInDays($end) > 62) {
            return $crm->client()->pagedScan(
                path: '/api/external/v1/session-presence',
                baseQuery: $baseQuery,
                pageSize: 25,
                maxPages: 80,
                concurrency: 3,
            );
        }

        $variants = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $variants[] = ['date' => $cursor->toDateString()];
            $cursor->addDay();
        }

        return $crm->client()->parallelFetch(
            path: '/api/external/v1/session-presence',
            baseQuery: $baseQuery,
            variantQueries: $variants,
            pageSize: 25,
            concurrency: 3,
        );
    }
}
