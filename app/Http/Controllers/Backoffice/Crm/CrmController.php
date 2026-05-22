<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\CrmException;
use App\Services\Crm\Stats\CrmStatsService;
use App\Services\Crm\Stats\DuplicateFinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRM overview controller.
 *
 * Lightweight pages that don't fit any specific domain: the landing index,
 * stats dashboard, duplicate detector, LOV browser, center-switcher form,
 * and the student type-ahead JSON endpoint. Per-domain CRUD-style pages
 * live in their own controllers (CrmStudentsController, CrmPaymentsController,
 * CrmGroupsController, CrmInsightsController).
 */
class CrmController extends BaseCrmController
{
    /** Persist the selected center in session, then redirect back. */
    public function setCenter(Request $r): RedirectResponse
    {
        $storeId = $r->input('crm_store_id');
        $this->centers->setStoreId($storeId ? (int) $storeId : null);

        return back();
    }

    public function index(): View
    {
        return $this->view('backoffice.crm.index');
    }

    /**
     * JSON endpoint that backs the student type-ahead in CRM filters.
     *
     * Accepts ?q=ahmed&strStoreId=42 and returns up to 25 students whose
     * first or last name contains the term. The /students endpoint accepts
     * firstName and lastName as separate filters, so we hit it twice and
     * merge.
     *
     * Response shape (matches the LOV provider's normalize() contract):
     *   [['id' => 123, 'name' => 'Ahmed Nadiri', 'reference' => '237SL126'], ...]
     */
    public function studentsSearch(Request $r): JsonResponse
    {
        $q = trim((string) $r->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $strStoreId = $this->currentStrStoreId();
        $crm        = $this->scopedCrm();

        $rows = [];
        $seen = [];

        $calls = [
            ['firstName' => $q, 'lastName'  => null],
            ['firstName' => null, 'lastName' => $q],
        ];

        foreach ($calls as $args) {
            try {
                $resp = $crm->students()->list(
                    page: 0,
                    size: 25,
                    includeTotal: false,
                    strStoreId: $strStoreId,
                    firstName: $args['firstName'],
                    lastName:  $args['lastName'],
                );
                foreach (($resp['data'] ?? []) as $row) {
                    $id = $row['ID'] ?? $row['STUDENT_ID'] ?? null;
                    if ($id === null || isset($seen[$id])) continue;
                    $seen[$id] = true;

                    $name = trim(($row['FIRST_NAME'] ?? '') . ' ' . ($row['LAST_NAME'] ?? '')) ?: ('#' . $id);
                    $rows[] = [
                        'id'        => (int) $id,
                        'name'      => $name,
                        'reference' => $row['REFERENCE'] ?? null,
                    ];
                }
            } catch (\Throwable) {
                // One failing call is non-fatal — we may still have results from the other.
            }
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        $rows = array_slice($rows, 0, 25);

        return response()->json(['data' => $rows]);
    }

    /**
     * Per-center evolution dashboard: KPI snapshot + monthly payments trend.
     */
    public function stats(Request $r, CrmStatsService $stats): View
    {
        $preset = $r->query('range', 'year');
        [$start, $end] = match ($preset) {
            'today' => [now()->toDateString(), now()->toDateString()],
            '7d'    => [now()->subDays(7)->toDateString(),  now()->toDateString()],
            '30d'   => [now()->subDays(30)->toDateString(), now()->toDateString()],
            'year'  => [now()->startOfYear()->toDateString(), now()->toDateString()],
            'custom' => [$r->query('startDate'), $r->query('endDate')],
            default  => [null, null],
        };

        $currentYear = (int) now()->year;
        $chartYear   = (int) $r->query('year', $currentYear);
        $chartYear   = max($currentYear - 5, min($chartYear, $currentYear + 1));
        $chartStore  = $this->currentStrStoreId();

        $kpis   = $stats->perCenterKpis($start, $end);
        $trend  = $stats->paymentsTrend(months: 6);
        $annual = $stats->annualSummary($chartYear, $chartStore);

        return $this->view('backoffice.crm.stats', [
            'kpis'        => $kpis,
            'trend'       => $trend,
            'preset'      => $preset,
            'start'       => $start,
            'end'         => $end,
            'annual'      => $annual,
            'chartYear'   => $chartYear,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Duplicate-detection scan over students returned by the API.
     * Buckets by phone / WhatsApp / email / CIN / (name + store).
     */
    public function duplicates(Request $r, DuplicateFinder $finder): View
    {
        $storeId   = $this->currentStrStoreId();
        $bustCache = $r->boolean('refresh');

        $report = $finder->find($storeId, bustCache: $bustCache);

        return $this->view('backoffice.crm.duplicates', [
            'report'  => $report,
            'storeId' => $storeId,
        ]);
    }

    public function lov(Request $r, string $kind): View
    {
        $method = match ($kind) {
            'school-levels'                          => 'schoolLevels',
            'registration-statuses'                  => 'registrationStatuses',
            'registration-conventions'               => 'registrationConventions',
            'registration-change-status-reasons'     => 'registrationChangeStatusReasons',
            'payment-types'                          => 'paymentTypes',
            'payment-statuses'                       => 'paymentStatuses',
            'payment-methods'                        => 'paymentMethods',
            'payment-check-statuses'                 => 'paymentCheckStatuses',
            'level-session-packages'                 => 'levelSessionPackages',
            'categories'                             => 'categories',
            'banks'                                  => 'banks',
            default                                  => abort(404),
        };

        $limit = (int) $r->query('limit', 100);

        $payload = null;
        $error   = null;
        try {
            $payload = $this->scopedCrm()->lov()->{$method}($limit);
        } catch (CrmException $e) {
            $error = $e;
        }

        return $this->view('backoffice.crm.lov', [
            'kind'    => $kind,
            'limit'   => $limit,
            'payload' => $payload,
            'error'   => $error,
        ]);
    }
}
