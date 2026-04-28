<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Encaissement\StoreSiteExpenseRequest;
use App\Models\SiteExpense;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteExpenseController extends Controller
{
    use ScopesToUserSites;

    public function index(Request $request)
    {
        $query = SiteExpense::with('site')->orderByDesc('month');

        $this->scopeToUserSites($query);

        $requestedSiteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        if ($requestedSiteId) {
            $query->where('site_id', $requestedSiteId);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month . '-01');
        }

        $expenses = $query->paginate(50)->withQueryString();
        $sites = $this->accessibleSites();

        return view('backoffice.encaissements.expenses.index', compact('expenses', 'sites'));
    }

    public function create()
    {
        $sites = $this->accessibleSites();

        return view('backoffice.encaissements.expenses.create', compact('sites'));
    }

    public function store(StoreSiteExpenseRequest $request)
    {
        SiteExpense::create($request->validated());

        return redirect()
            ->route('backoffice.encaissements.expenses.index')
            ->with('success', 'Charge ajoutée avec succès.');
    }

    public function edit(SiteExpense $expense)
    {
        $sites = $this->accessibleSites();

        return view('backoffice.encaissements.expenses.edit', compact('expense', 'sites'));
    }

    public function update(StoreSiteExpenseRequest $request, SiteExpense $expense)
    {
        $expense->update($request->validated());

        return redirect()
            ->route('backoffice.encaissements.expenses.index')
            ->with('success', 'Charge mise à jour.');
    }

    public function destroy(SiteExpense $expense)
    {
        $expense->delete();

        return redirect()
            ->route('backoffice.encaissements.expenses.index')
            ->with('success', 'Charge supprimée.');
    }
}
