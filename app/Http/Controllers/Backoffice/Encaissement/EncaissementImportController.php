<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Encaissement\ImportEncaissementRequest;
use App\Models\EncaissementImport;
use App\Models\Site;
use App\Services\Encaissement\EncaissementImportService;
use Illuminate\Http\Request;

class EncaissementImportController extends Controller
{
    use ScopesToUserSites;

    public function __construct(
        private EncaissementImportService $importService
    ) {}

    /**
     * Import history list.
     */
    public function index(Request $request)
    {
        $query = EncaissementImport::with(['site', 'importedBy'])
            ->orderByDesc('created_at');

        $this->scopeToUserSites($query);

        $requestedSiteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        if ($requestedSiteId) {
            $query->where('site_id', $requestedSiteId);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $imports = $query->paginate(30)->withQueryString();
        $sites = $this->accessibleSites();

        return view('backoffice.encaissements.imports.index', compact('imports', 'sites'));
    }

    /**
     * Show import form.
     */
    public function create()
    {
        $sites = $this->accessibleSites();

        return view('backoffice.encaissements.imports.create', compact('sites'));
    }

    /**
     * Preview parsed data before import.
     */
    public function preview(ImportEncaissementRequest $request)
    {
        $result = $this->importService->preview(
            $request->file('file'),
            $request->site_id,
            $request->source_system,
            $request->school_year,
        );

        $sites = $this->accessibleSites();
        $site = Site::findOrFail($request->site_id);

        // Store file temporarily for the confirm step
        $tempPath = $request->file('file')->store('encaissement-temp', 'local');

        return view('backoffice.encaissements.imports.preview', compact(
            'result', 'sites', 'site', 'tempPath'
        ))->with([
            'source_system' => $request->source_system,
            'school_year' => $request->school_year,
            'site_id' => $request->site_id,
            'notes' => $request->notes,
        ]);
    }

    /**
     * Confirm and execute the import.
     */
    public function store(ImportEncaissementRequest $request)
    {
        $import = $this->importService->import(
            $request->file('file'),
            $request->site_id,
            $request->source_system,
            $request->school_year,
            auth()->id(),
            $request->notes,
            $request->month,
        );

        return redirect()
            ->route('backoffice.encaissements.imports.show', $import)
            ->with('success', sprintf(
                'Import terminé : %d lignes importées, %d doublons, %d erreurs. Total : %s DH',
                $import->success_rows,
                $import->duplicate_rows,
                $import->error_rows,
                number_format($import->total_amount, 2, ',', ' ')
            ));
    }

    /**
     * Show import detail with stats.
     */
    public function show(EncaissementImport $import)
    {
        $import->load(['site', 'importedBy']);

        $encaissements = $import->encaissements()
            ->orderBy('collected_at')
            ->orderBy('order_number')
            ->paginate(100);

        // Summary totals by payment method (like the original CRM report)
        $byMethod = $import->encaissements()
            ->select('payment_method', \DB::raw('SUM(amount) as total'), \DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        // Summary totals by fee type
        $byFeeType = $import->encaissements()
            ->select('fee_type', \DB::raw('SUM(amount) as total'), \DB::raw('COUNT(*) as count'))
            ->groupBy('fee_type')
            ->get()
            ->keyBy('fee_type');

        return view('backoffice.encaissements.imports.show', compact('import', 'encaissements', 'byMethod', 'byFeeType'));
    }

    /**
     * Delete import and all its encaissements.
     */
    public function destroy(EncaissementImport $import)
    {
        // Delete associated encaissements first
        $import->encaissements()->delete();
        $import->delete();

        return redirect()
            ->route('backoffice.encaissements.imports.index')
            ->with('success', 'Import et ses encaissements supprimés.');
    }
}
