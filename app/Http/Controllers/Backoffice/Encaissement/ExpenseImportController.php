<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\ExpenseImport;
use App\Models\Site;
use App\Services\Encaissement\ExpenseImportService;
use Illuminate\Http\Request;

class ExpenseImportController extends Controller
{
    use ScopesToUserSites;

    public function __construct(
        private ExpenseImportService $importService
    ) {}

    public function index(Request $request)
    {
        $query = ExpenseImport::with(['site', 'importedBy'])
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

        return view('backoffice.encaissements.expenses.imports', compact('imports', 'sites'));
    }

    public function create()
    {
        $sites = $this->accessibleSites();

        return view('backoffice.encaissements.expenses.import-create', compact('sites'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'month'   => ['required', 'string', 'max:7', 'regex:#^\d{4}-(0[1-9]|1[0-2])$#'],
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
        );

        return redirect()
            ->route('backoffice.encaissements.expenses.imports.show', $import)
            ->with('success', sprintf(
                'Import terminé : %d dépenses importées. Total : %s DH',
                $import->success_rows,
                number_format($import->total_amount, 2, ',', ' ')
            ));
    }

    public function show(ExpenseImport $import)
    {
        $import->load(['site', 'importedBy']);

        $expenses = $import->expenses()
            ->orderBy('expense_date')
            ->orderBy('order_number')
            ->paginate(100);

        // Summary by type
        $byType = $import->expenses()
            ->select('type', \DB::raw('SUM(amount) as total'), \DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return view('backoffice.encaissements.expenses.import-show', compact('import', 'expenses', 'byType'));
    }

    public function destroy(ExpenseImport $import)
    {
        $import->expenses()->delete();
        $import->delete();

        return redirect()
            ->route('backoffice.encaissements.expenses.imports.index')
            ->with('success', 'Import et ses dépenses supprimés.');
    }
}
