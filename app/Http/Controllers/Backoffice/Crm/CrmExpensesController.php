<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\Site;
use App\Models\SiteExpense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class CrmExpensesController extends BaseCrmController
{
    public function index(Request $request): View
    {
        $siteId   = $request->filled('site_id') ? (int) $request->site_id : null;
        $month    = $request->filled('month')   ? $request->month         : null;
        $typeKey  = $request->filled('type')    ? $request->type          : null;

        $baseQuery = SiteExpense::where('crm_source', 'wimschool');
        if ($siteId) $baseQuery->where('site_id', $siteId);
        if ($month)  $baseQuery->where('month', $month . '-01');
        if ($typeKey) $baseQuery->where('type', $typeKey);

        $query = (clone $baseQuery)->with('site')
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        $expenses = $query->paginate(50)->withQueryString();

        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get(['id', 'name']);

        // Monthly totals per site for the bar chart — last 6 months
        $chartMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $chartMonths->push(Carbon::now('Africa/Casablanca')->subMonths($i)->format('Y-m'));
        }

        $rawTotals = SiteExpense::where('crm_source', 'wimschool')
            ->selectRaw("DATE_FORMAT(month, '%Y-%m') as ym, site_id, SUM(amount) as total")
            ->whereIn(DB::raw("DATE_FORMAT(month, '%Y-%m')"), $chartMonths->toArray())
            ->groupBy('ym', 'site_id')
            ->get();

        $seriesMap = [];
        foreach ($rawTotals as $row) {
            $site = $sites->firstWhere('id', $row->site_id);
            $name = $site ? $site->name : "Site #{$row->site_id}";
            $seriesMap[$name][$row->ym] = (float) $row->total;
        }

        $chartSeries = [];
        foreach ($seriesMap as $siteName => $byMonth) {
            $chartSeries[] = [
                'name' => $siteName,
                'data' => $chartMonths->map(fn ($m) => $byMonth[$m] ?? 0)->values()->toArray(),
            ];
        }

        // Per-center totals ranked
        $summaryQuery = SiteExpense::where('crm_source', 'wimschool')
            ->join('sites', 'sites.id', '=', 'site_expenses.site_id')
            ->selectRaw('sites.name as site_name, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('sites.name')
            ->orderByDesc('total');
        if ($month) $summaryQuery->where('month', $month . '-01');

        $centerSummary = $summaryQuery->get();
        $maxTotal      = $centerSummary->max('total') ?: 1;

        // Per-type breakdown (from local DB — uses SiteExpense::TYPES labels)
        $typeBreakdownQ = SiteExpense::where('crm_source', 'wimschool')
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->orderByDesc('total');
        if ($siteId) $typeBreakdownQ->where('site_id', $siteId);
        if ($month)  $typeBreakdownQ->where('month', $month . '-01');

        $typeBreakdown = $typeBreakdownQ->get()->map(fn($r) => [
            'key'   => $r->type,
            'label' => SiteExpense::TYPES[$r->type] ?? $r->type,
            'count' => (int) $r->count,
            'total' => (float) $r->total,
        ])->values()->toArray();

        $maxTypeTotal = collect($typeBreakdown)->max('total') ?: 1;

        // Expense types from CRM LOV (for filter dropdown) — cached locally
        $lovTypes = Cache::remember('crm.lov.expense_types', 600, function () {
            try {
                return $this->lovs->expenseTypes();
            } catch (\Throwable) {
                return [];
            }
        });

        return $this->view('backoffice.crm.expenses.index', [
            'expenses'      => $expenses,
            'sites'         => $sites,
            'selectedSite'  => $siteId,
            'selectedMonth' => $month,
            'selectedType'  => $typeKey,
            'chartMonths'   => $chartMonths->toArray(),
            'chartSeries'   => $chartSeries,
            'centerSummary' => $centerSummary,
            'maxTotal'      => $maxTotal,
            'typeBreakdown' => $typeBreakdown,
            'maxTypeTotal'  => $maxTypeTotal,
            'lovTypes'      => $lovTypes,
        ]);
    }
}
