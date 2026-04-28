<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\Encaissement\PrimeCalculationService;
use Illuminate\Http\Request;

class RecouvrementController extends Controller
{
    use ScopesToUserSites;

    public function __construct(
        private PrimeCalculationService $primeService
    ) {}

    /**
     * État de recouvrement — all centers with CA, impayés, collection rate.
     */
    public function index(Request $request)
    {
        // Fallback to current month if missing or invalid
        $month = $request->get('month') ?: now()->format('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $defaultPeriodMonths = $this->primeService->getDefaultPeriodMonths();
        // Allow overriding the period from the UI
        $periodMonths = (int) ($request->get('period_months') ?: $defaultPeriodMonths);
        if (!in_array($periodMonths, [1, 3, 6, 12])) {
            $periodMonths = $defaultPeriodMonths;
        }

        $sites = $this->accessibleSites();

        // Get state for each center (with period) — then filter to accessible ones
        $allStates = $this->primeService->getAllCentersRecoveryState($month, $periodMonths);

        if ($this->userSeesAllSites()) {
            $states = $allStates;
        } else {
            $allowed = auth()->user()->accessibleSiteIds();
            $states = array_values(array_filter(
                $allStates,
                fn ($s) => in_array((int) ($s['site_id'] ?? 0), $allowed, true)
            ));
        }

        // Prime suggestions for each center (with period)
        $suggestions = [];
        foreach ($states as $state) {
            if ($state['ca'] > 0) {
                $suggestions[$state['site_id']] = $this->primeService->suggestPrimeForCenter(
                    $state['site_id'], $month, $periodMonths
                );
            }
        }

        // Overall totals
        $totalEncaissement = array_sum(array_column($states, 'encaissement'));
        $totalImpaye = array_sum(array_column($states, 'impaye'));
        $totalCA = array_sum(array_column($states, 'ca'));
        $globalRate = $totalCA > 0 ? round(($totalEncaissement / $totalCA) * 100, 2) : 0;

        return view('backoffice.encaissements.recouvrement', compact(
            'states', 'suggestions', 'sites', 'month',
            'totalEncaissement', 'totalImpaye', 'totalCA', 'globalRate',
            'defaultPeriodMonths', 'periodMonths'
        ));
    }

    /**
     * Generate auto primes for a center based on collection rate.
     */
    public function generatePrimes(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'month'   => ['required', 'string', 'max:7', 'regex:#^\d{4}-(0[1-9]|1[0-2])$#'],
            'period_months' => 'nullable|integer|in:1,3,6,12',
        ]);

        $created = $this->primeService->generatePrimesForCenter(
            $request->site_id,
            $request->month,
            auth()->id(),
            $request->period_months ? (int) $request->period_months : null,
        );

        if ($created === 0) {
            return redirect()->back()->with('error',
                'Aucune prime générée. Le centre n\'est pas éligible ou les primes existent déjà pour ce mois.'
            );
        }

        return redirect()->back()->with('success',
            $created . ' prime(s) générée(s) automatiquement pour ce centre.'
        );
    }
}
