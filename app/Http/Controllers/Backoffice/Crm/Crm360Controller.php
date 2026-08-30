<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Exports\Crm360Workbook;
use App\Services\Crm\Unified360Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Vue 360 — the single table that links Étudiant → Inscription → Groupe →
 * Paiement, with an Excel export of exactly what is on screen.
 *
 * Everything is served from the local CRM mirror tables via Unified360Service,
 * so no Wimschool API call happens on this page — the export can therefore
 * safely walk the entire filtered result set.
 */
class Crm360Controller extends BaseCrmController
{
    /** Filter keys read off the request, shared by the page, the API and the export. */
    private const FILTER_KEYS = [
        'search', 'classId', 'studentId', 'registrationStatus',
        'paymentMethod', 'paymentType', 'startDate', 'endDate',
        'regStartDate', 'regEndDate', 'paymentPresence', 'unpaidOnly',
    ];

    public function __construct(
        \App\Services\Crm\Crm $crm,
        \App\Services\Crm\CenterContext $centers,
        \App\Services\Crm\CrmLovProvider $lovs,
        private Unified360Service $service,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    public function index(Request $r): View
    {
        $filters = $this->filters($r);
        $perPage = min(200, max(10, (int) $r->query('perPage', 50)));

        $rows = $this->service->query($filters)
            ->paginate($perPage)
            ->withQueryString();

        return $this->view('backoffice.crm.unified-360', [
            'rows' => $rows,
            'columns' => Unified360Service::COLUMNS,
            'totals' => $this->service->totals($filters),
            'options' => $this->service->filterOptions($filters['strStoreId'] ?? null),
            'filters' => $r->query(),
            'perPage' => $perPage,
        ]);
    }

    /** JSON feed of the same rows — for dashboards or an external consumer. */
    public function data(Request $r): JsonResponse
    {
        $filters = $this->filters($r);
        $perPage = min(500, max(10, (int) $r->query('perPage', 100)));

        return response()->json([
            'totals' => $this->service->totals($filters),
            'data' => $this->service->query($filters)->paginate($perPage)->withQueryString(),
        ]);
    }

    public function export(Request $r): BinaryFileResponse
    {
        $filters = $this->filters($r);
        $name = 'gls-vue360-'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new Crm360Workbook($this->service, $filters), $name);
    }

    /**
     * Collect the filter bag, always seeding the active centre so the page
     * respects the centre selector at the top like every other CRM page.
     *
     * @return array<string,mixed>
     */
    private function filters(Request $r): array
    {
        $filters = ['strStoreId' => $this->currentStrStoreId()];

        foreach (self::FILTER_KEYS as $key) {
            $value = $r->query($key);
            if ($value !== null && $value !== '') {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }
}
