<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Encaissement\StoreEncaissementRequest;
use App\Models\Encaissement;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

class EncaissementController extends Controller
{
    use ScopesToUserSites;

    /**
     * List encaissements with filters.
     */
    public function index(Request $request)
    {
        $query = Encaissement::with('site')
            ->orderByDesc('collected_at')
            ->orderByDesc('id');

        // Centre access scope (Super Admin / Admin: no scope; others: pivot)
        $this->scopeToUserSites($query);

        // Filters
        $requestedSiteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        if ($requestedSiteId) {
            $query->where('site_id', $requestedSiteId);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('fee_type')) {
            $query->where('fee_type', $request->fee_type);
        }
        if ($request->filled('operator')) {
            $query->where('operator_name', 'like', '%' . $request->operator . '%');
        }
        if ($request->filled('date_from')) {
            $query->where('collected_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('collected_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('payer_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('source_system')) {
            $query->where('source_system', $request->source_system);
        }

        $encaissements = $query->paginate(50)->withQueryString();
        $sites = $this->accessibleSites();

        // Quick totals for current filters
        $totals = (clone $query)->getQuery();
        $totalAmount = Encaissement::fromSub($totals, 'filtered')->sum('amount');

        return view('backoffice.encaissements.index', compact('encaissements', 'sites', 'totalAmount'));
    }

    /**
     * Show create form for manual entry.
     */
    public function create()
    {
        $sites = $this->accessibleSites();
        $employees = User::whereNotNull('staff_role')->where('is_active', true)->orderBy('name')->get();

        return view('backoffice.encaissements.create', compact('sites', 'employees'));
    }

    /**
     * Store manual encaissement.
     */
    public function store(StoreEncaissementRequest $request)
    {
        $data = $request->validated();
        $data['source_system'] = 'manual';

        Encaissement::create($data);

        return redirect()
            ->route('backoffice.encaissements.index')
            ->with('success', 'Encaissement ajouté avec succès.');
    }

    /**
     * Show encaissement detail.
     */
    public function show(Encaissement $encaissement)
    {
        $encaissement->load('site', 'import', 'user');

        return view('backoffice.encaissements.show', compact('encaissement'));
    }

    /**
     * Edit form.
     */
    public function edit(Encaissement $encaissement)
    {
        // Block users that aren't allowed to manage this centre.
        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $encaissement->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        $sites = $this->accessibleSites();
        $employees = User::whereNotNull('staff_role')->where('is_active', true)->orderBy('name')->get();

        return view('backoffice.encaissements.edit', compact('encaissement', 'sites', 'employees'));
    }

    /**
     * Update encaissement.
     */
    public function update(StoreEncaissementRequest $request, Encaissement $encaissement)
    {
        $encaissement->update($request->validated());

        return redirect()
            ->route('backoffice.encaissements.index')
            ->with('success', 'Encaissement mis à jour.');
    }

    /**
     * Delete encaissement.
     */
    public function destroy(Encaissement $encaissement)
    {
        $encaissement->delete();

        return redirect()
            ->route('backoffice.encaissements.index')
            ->with('success', 'Encaissement supprimé.');
    }
}
