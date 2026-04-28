<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Prime;
use App\Models\Site;
use App\Models\User;
use App\Models\SystemConfig;
use Illuminate\Http\Request;

class PrimeController extends Controller
{
    use ScopesToUserSites;

    public function index(Request $request)
    {
        $query = Prime::with(['user', 'site', 'approvedBy'])
            ->orderByDesc('month');

        $this->scopeToUserSites($query);

        $requestedSiteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        if ($requestedSiteId) {
            $query->where('site_id', $requestedSiteId);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif ($request->filled('employee_id')) {
            // BC: legacy filter name
            $query->where('user_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $request->status === 'approved' ? $query->approved() : $query->pending();
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month . '-01');
        }

        $primes = $query->paginate(50)->withQueryString();
        $sites = $this->accessibleSites();
        $employees = User::whereNotNull('staff_role')->where('is_active', true)->orderBy('name')->get();

        // Stats
        $totalAmount = (clone $query)->sum('amount');
        $autoCount = (clone $query)->where('auto_generated', true)->count();

        return view('backoffice.encaissements.primes.index', compact(
            'primes', 'sites', 'employees', 'totalAmount', 'autoCount'
        ));
    }

    /**
     * Approve a prime.
     */
    public function approve(Prime $prime)
    {
        $prime->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Prime approuvée.');
    }

    /**
     * Reject (delete) a non-approved prime.
     */
    public function destroy(Prime $prime)
    {
        if ($prime->isApproved()) {
            return redirect()->back()->with('error', 'Impossible de supprimer une prime déjà approuvée.');
        }

        $prime->delete();

        return redirect()
            ->route('backoffice.encaissements.primes.index')
            ->with('success', 'Prime supprimée.');
    }

    /**
     * Show config page for prime rules.
     */
    public function config()
    {
        $config = [
            'period_months' => SystemConfig::get('prime.period_months', 1),
            'threshold_rate' => SystemConfig::get('prime.threshold_rate', 70),
            'amount_per_point' => SystemConfig::get('prime.amount_per_point', 200),
            'eligible_roles' => SystemConfig::get('prime.eligible_roles', ['Réception', 'Commercial', 'Coordination']),
        ];

        return view('backoffice.encaissements.primes.config', compact('config'));
    }

    /**
     * Update prime configuration.
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'period_months' => 'required|integer|in:1,3,6,12',
            'threshold_rate' => 'required|integer|min:0|max:100',
            'amount_per_point' => 'required|integer|min:0',
            'eligible_roles' => 'required|array|min:1',
            'eligible_roles.*' => 'string|in:Administration,Réception,Commercial,Manager,Coordination,Autre',
        ]);

        SystemConfig::set('prime.period_months', $request->period_months);
        SystemConfig::set('prime.threshold_rate', $request->threshold_rate);
        SystemConfig::set('prime.amount_per_point', $request->amount_per_point);
        SystemConfig::set('prime.eligible_roles', $request->eligible_roles);

        return redirect()
            ->route('backoffice.encaissements.primes.config')
            ->with('success', 'Configuration des primes mise à jour.');
    }
}
