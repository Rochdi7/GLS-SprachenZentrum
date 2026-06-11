<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\Encaissement\EncaissementAnalyticsService;
use Illuminate\Http\Request;

class EncaissementDashboardController extends Controller
{
    use ScopesToUserSites;

    public function __construct(
        private EncaissementAnalyticsService $analytics
    ) {}

    /**
     * Main encaissement dashboard.
     */
    public function index(Request $request)
    {
        // Year (required, default current). Month is optional: 1-12, blank = whole year.
        $year = $request->get('year');
        if (!preg_match('/^\d{4}$/', (string) $year)) {
            $year = now()->format('Y');
        }

        $monthNum = $request->get('month_num');
        if (!preg_match('/^(0?[1-9]|1[0-2])$/', (string) $monthNum)) {
            $monthNum = '';
        }

        // Period passed to analytics: 'YYYY' for full year, 'YYYY-MM' for a specific month.
        $period = $monthNum !== ''
            ? sprintf('%s-%02d', $year, (int) $monthNum)
            : $year;

        $siteId = $request->get('site_id');
        $sid = $this->resolveRequestedSiteId($siteId ? (int) $siteId : null);

        // Non-admin users without an explicit centre selection: lock to their first centre
        // so analytics don't leak data from centres they're not affected to.
        if (! $this->userSeesAllSites() && $sid === null) {
            $allowed = auth()->user()->accessibleSiteIds();
            $sid = $allowed[0] ?? null;
        }

        $data = $this->analytics->getDashboardData($sid, $period);
        $sites = $this->accessibleSites();

        // Chart data
        $monthlyEvolution = $this->analytics->getMonthlyEvolution($sid, 12);
        $methodEvolution = $this->analytics->getMethodEvolution($sid, 6);

        // Backward-compat for existing view bits that still read $month
        $month = $period;

        return view('backoffice.encaissements.dashboard', compact(
            'data', 'sites', 'month', 'year', 'monthNum', 'siteId', 'monthlyEvolution', 'methodEvolution'
        ));
    }

    /**
     * Rentability dashboard.
     */
    public function rentabilite(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }
        $siteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        $sites = $this->accessibleSites();

        $rentabilite = null;
        $history = [];
        $comparison = [];

        $monthlyEvolution = [];
        if ($siteId) {
            $rentabilite = $this->analytics->getRentabilite((int) $siteId, $month);
            $history = $this->analytics->getRentabiliteHistory((int) $siteId, 6);
            $monthlyEvolution = $this->analytics->getMonthlyEvolution((int) $siteId, 12);
        }

        // Centre comparison — restricted to accessible centres for non-admins.
        $allComparison = $this->analytics->compareSites($month);
        if ($this->userSeesAllSites()) {
            $comparison = $allComparison;
        } else {
            $allowed = auth()->user()->accessibleSiteIds();
            $comparison = array_values(array_filter(
                $allComparison,
                fn ($r) => in_array((int) ($r['site_id'] ?? $r->site_id ?? 0), $allowed, true)
            ));
        }

        return view('backoffice.encaissements.rentabilite', compact(
            'sites', 'month', 'siteId', 'rentabilite', 'history', 'comparison', 'monthlyEvolution'
        ));
    }

    /**
     * CA par groupe et par jour — source: CRM API snapshots.
     */
    public function caGroupes(Request $request)
    {
        $sites  = $this->accessibleSites();
        $siteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );

        if (!$this->userSeesAllSites() && $siteId === null) {
            $allowed = auth()->user()->accessibleSiteIds();
            $siteId = $allowed[0] ?? null;
        }

        // Available months from CRM snapshot data (last 24 months max)
        $availableMonths = \Illuminate\Support\Facades\DB::table('crm_payment_snapshots')
            ->selectRaw("DATE_FORMAT(effective_date, '%Y-%m') as month")
            ->whereNotNull('effective_date')
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(24)
            ->pluck('month')
            ->toArray();

        // Default to most recent month that has data
        $defaultMonth = $availableMonths[0] ?? now()->format('Y-m');

        $month = $request->get('month') ?: $defaultMonth;
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)
            && !preg_match('/^\d{4}$/', $month)) {
            $month = $defaultMonth;
        }

        $rows = $this->analytics->getCaParGroupeParJour($siteId, $month);

        $byGroup = [];
        $allDays = [];
        foreach ($rows as $row) {
            $byGroup[$row->group_name][$row->day] = [
                'total' => (float) $row->total,
                'count' => (int)   $row->count,
            ];
            $allDays[$row->day] = true;
        }
        ksort($byGroup);
        ksort($allDays);
        $allDays = array_keys($allDays);

        return view('backoffice.encaissements.ca-groupes', compact(
            'sites', 'month', 'siteId', 'byGroup', 'allDays', 'availableMonths'
        ));
    }

    /**
     * Operator performance page.
     */
    public function operators(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }
        $siteId = $this->resolveRequestedSiteId(
            $request->filled('site_id') ? (int) $request->site_id : null
        );
        $sites = $this->accessibleSites();

        // Non-admin users without an explicit centre: default to first centre
        if (! $this->userSeesAllSites() && $siteId === null) {
            $allowed = auth()->user()->accessibleSiteIds();
            $siteId = $allowed[0] ?? null;
        }

        $operators = $this->analytics->getOperatorPerformance($siteId, $month);

        return view('backoffice.encaissements.operators', compact('sites', 'month', 'siteId', 'operators'));
    }
}
