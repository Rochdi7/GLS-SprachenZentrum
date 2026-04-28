<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\ImpayeImport;
use App\Models\Impaye;
use App\Models\Site;
use App\Services\Encaissement\ImpayeImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImpayeImportController extends Controller
{
    use ScopesToUserSites;

    public function __construct(
        private ImpayeImportService $importService
    ) {}

    public function index(Request $request)
    {
        $query = ImpayeImport::with(['site', 'importedBy'])->orderByDesc('created_at');

        $this->scopeToUserSites($query);

        $requestedSiteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        if ($requestedSiteId) $query->where('site_id', $requestedSiteId);
        if ($request->filled('month')) $query->where('month', $request->month);

        $imports = $query->paginate(30)->withQueryString();
        $sites = $this->accessibleSites();

        return view('backoffice.encaissements.impayes.imports', compact('imports', 'sites'));
    }

    public function create()
    {
        $sites = $this->accessibleSites();
        return view('backoffice.encaissements.impayes.import-create', compact('sites'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'month'   => ['required', 'string', 'max:7', 'regex:#^\d{4}-(0[1-9]|1[0-2])$#'],
            'snapshot_date' => 'required|date',
            'file'    => 'required|file|max:20480',
            'notes'   => 'nullable|string|max:2000',
        ]);

        $validator->after(function ($v) use ($request) {
            $file = $request->file('file');
            if ($file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls', 'csv', 'pdf'])) {
                    $v->errors()->add('file', 'Le fichier doit être Excel (.xlsx, .xls, .csv) ou PDF.');
                }
            }
        });

        $validator->validate();

        $import = $this->importService->import(
            $request->file('file'),
            $request->site_id,
            $request->month,
            auth()->id(),
            $request->notes,
            $request->snapshot_date,
        );

        return redirect()
            ->route('backoffice.encaissements.impayes.imports.show', $import)
            ->with('success', sprintf(
                'Import terminé : %d impayés importés. Total à recouvrer : %s DH',
                $import->success_rows,
                number_format($import->total_amount, 2, ',', ' ')
            ));
    }

    public function show(ImpayeImport $import)
    {
        $import->load(['site', 'importedBy']);
        $impayes = $import->impayes()->orderBy('order_number')->paginate(100);

        return view('backoffice.encaissements.impayes.import-show', compact('import', 'impayes'));
    }

    public function destroy(ImpayeImport $import)
    {
        $import->impayes()->delete();
        $import->delete();

        return redirect()
            ->route('backoffice.encaissements.impayes.imports.index')
            ->with('success', 'Import et ses impayés supprimés.');
    }

    /**
     * Mark an impaye as recovered.
     */
    public function markRecovered(Impaye $impaye)
    {
        $impaye->markRecovered();
        return redirect()->back()->with('success', 'Impayé marqué comme recouvré.');
    }
}
