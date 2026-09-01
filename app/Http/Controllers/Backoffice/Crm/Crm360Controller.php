<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Exports\Crm360Workbook;
use App\Services\Crm\Unified360Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $rows = $this->service
            ->page($filters, $perPage, (int) $r->query('page', 1))
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

    /**
     * Rows above which the export switches from .xlsx to streamed CSV.
     *
     * Building the .xlsx is the slow half, not the SQL: at ~15.6k rows the
     * query takes ~1.5s while PhpSpreadsheet takes ~51s to assemble the
     * workbook in memory — already brushing nginx's default 60s
     * proxy_read_timeout, beyond which the user gets a 504 instead of a file.
     * Live data sits at ~28k rows, so a full unfiltered .xlsx cannot finish
     * inside the window.
     *
     * CSV has no such wall: rows are written to the response as the query
     * streams them, first byte in under a second regardless of size. Excel
     * opens it directly (UTF-8 BOM + semicolon delimiter for the FR locale).
     * The trade-off is one flat sheet — no Résumé tab, no styling — which is
     * why small exports keep the nicer workbook.
     */
    private const XLSX_MAX_ROWS = 20000;

    public function export(Request $r): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($r);
        $rows = $this->service->totals($filters)['rows'];
        $stamp = now()->format('Y-m-d_His');

        if ($rows > self::XLSX_MAX_ROWS) {
            return $this->exportCsv($filters, "gls-vue360-{$stamp}.csv");
        }

        // The workbook is assembled in memory before the first byte is sent —
        // give the writer more headroom than a normal request.
        set_time_limit(600);

        return Excel::download(new Crm360Workbook($this->service, $filters), "gls-vue360-{$stamp}.xlsx");
    }

    /**
     * Stream the detail table as CSV, row by row, straight from the cursor.
     * Nothing is buffered, so size only affects transfer time — not memory,
     * and not time-to-first-byte.
     *
     * @param  array<string,mixed>  $filters
     */
    private function exportCsv(array $filters, string $name): StreamedResponse
    {
        set_time_limit(600);
        $columns = Unified360Service::COLUMNS;

        return response()->streamDownload(function () use ($filters, $columns) {
            $out = fopen('php://output', 'w');

            // BOM so Excel reads the accents; semicolon so the FR locale
            // splits columns instead of dumping everything into column A.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_values($columns), ';');

            foreach ($this->service->query($filters)->cursor() as $row) {
                $line = [];
                foreach (array_keys($columns) as $field) {
                    $value = $row->{$field} ?? '';
                    // Same date presentation as the xlsx export.
                    if ($value && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value)) {
                        $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
                    }
                    $line[] = $value;
                }
                fputcsv($out, $line, ';');
            }

            fclose($out);
        }, $name, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Accel-Buffering' => 'no', // let nginx flush as we write
        ]);
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
