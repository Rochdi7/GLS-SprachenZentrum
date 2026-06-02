<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmChurnScore;
use App\Models\Site;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ChurnController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    /**
     * List all churn scores with filters and summary cards.
     */
    public function index(Request $request): View
    {
        $query = CrmChurnScore::query()->orderByDesc('score');

        if ($request->filled('crm_store_id')) {
            $query->where('crm_store_id', (int) $request->crm_store_id);
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $scores = $query->paginate(50)->withQueryString();

        // Summary counts per risk level
        $summaryQuery = CrmChurnScore::query();
        if ($request->filled('crm_store_id')) {
            $summaryQuery->where('crm_store_id', (int) $request->crm_store_id);
        }
        $summary = $summaryQuery->selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level')
            ->toArray();

        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get();

        return $this->view('backoffice.crm.churn.index', compact('scores', 'summary', 'sites'));
    }

    /**
     * Show one student's churn signals breakdown.
     */
    public function show(Request $request, int $studentId): View
    {
        $score = CrmChurnScore::where('crm_student_id', $studentId)->firstOrFail();

        return $this->view('backoffice.crm.churn.show', compact('score'));
    }

    /**
     * Dispatch the churn recompute command asynchronously and redirect back.
     */
    public function recompute(Request $request): RedirectResponse
    {
        Artisan::queue('crm:churn-scores', ['--all' => true]);

        return redirect()
            ->route('backoffice.crm.churn.index')
            ->with('success', 'Recalcul des scores en cours. Les résultats seront disponibles dans quelques minutes.');
    }
}
