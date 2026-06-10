<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\Site;
use App\Models\SiteExpense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CrmExpensesController extends BaseCrmController
{
    public function index(Request $request): View
    {
        $siteId = $request->filled('site_id') ? (int) $request->site_id : null;
        $month  = $request->filled('month')   ? $request->month         : null;

        $query = SiteExpense::with('site')
            ->where('crm_source', 'wimschool')
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        if ($month) {
            $query->where('month', $month . '-01');
        }

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

        // Build series: [ { name: siteName, data: [total, ...] } ]
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

        return $this->view('backoffice.crm.expenses.index', [
            'expenses'     => $expenses,
            'sites'        => $sites,
            'selectedSite' => $siteId,
            'selectedMonth'=> $month,
            'chartMonths'  => $chartMonths->toArray(),
            'chartSeries'  => $chartSeries,
        ]);
    }
}
