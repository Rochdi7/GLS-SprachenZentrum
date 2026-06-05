<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\AgentFollowUp;
use App\Models\CrmChurnScore;
use App\Models\CrmCollectionRow;
use App\Models\CrmRegistration;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Agent / Call-Center dashboard.
 *
 * All data comes from local DB tables — zero API calls.
 * Routes are under backoffice.crm.agent.*
 */
class AgentDashboardController extends BaseCrmController
{
    // ── Main dashboard ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $storeId   = $this->currentStrStoreId();
        $riskFilter = $request->query('risk_level');
        $agentFilter = $request->query('agent_id') ? (int) $request->query('agent_id') : null;

        // KPI counts
        $kpis = $this->buildKpis($storeId);

        // Main risk table — paginated
        $scores = CrmChurnScore::query()
            ->forStore($storeId)
            ->when($riskFilter, fn($q) => $q->where('risk_level', $riskFilter))
            ->orderByRaw("FIELD(risk_level,'critical','high','medium','low')")
            ->orderByDesc('score')
            ->paginate(40)
            ->withQueryString();

        // Attach latest follow-up per student for the current page
        $studentIds = $scores->pluck('crm_student_id')->all();
        $latestFollowUps = AgentFollowUp::whereIn('crm_student_id', $studentIds)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('crm_student_id')
            ->map->first();

        // Agents list for filter
        $agents = User::orderBy('name')->get(['id', 'name']);

        // Centers for filter
        $centers = $this->centers->available();

        return $this->view('backoffice.crm.agent.index', [
            'scores'         => $scores,
            'latestFollowUps' => $latestFollowUps,
            'kpis'           => $kpis,
            'agents'         => $agents,
            'storeId'        => $storeId,
            'riskFilter'     => $riskFilter,
            'agentFilter'    => $agentFilter,
            'statuses'       => AgentFollowUp::STATUSES,
        ]);
    }

    // ── Students to call today ────────────────────────────────────────────────

    public function callToday(Request $request)
    {
        $storeId = $this->currentStrStoreId();

        // High/critical risk with NO follow-up in the last 7 days
        $recentlyContacted = AgentFollowUp::where('created_at', '>=', now()->subDays(7))
            ->pluck('crm_student_id')
            ->unique()
            ->values()
            ->all();

        $scores = CrmChurnScore::query()
            ->forStore($storeId)
            ->whereIn('risk_level', ['high', 'critical'])
            ->whereNotIn('crm_student_id', $recentlyContacted)
            ->orderByRaw("FIELD(risk_level,'critical','high')")
            ->orderByDesc('score')
            ->paginate(30)
            ->withQueryString();

        // Follow-ups due today (regardless of risk level)
        $followUpsToday = AgentFollowUp::with('agent')
            ->dueToday()
            ->where('status', '!=', 'solved')
            ->orderBy('follow_up_date')
            ->get();

        return $this->view('backoffice.crm.agent.call-today', [
            'scores'         => $scores,
            'followUpsToday' => $followUpsToday,
            'storeId'        => $storeId,
        ]);
    }

    // ── Follow-up history ─────────────────────────────────────────────────────

    public function followUps(Request $request)
    {
        $storeId     = $this->currentStrStoreId();
        $statusFilter = $request->query('status');
        $agentFilter  = $request->query('agent_id') ? (int) $request->query('agent_id') : null;

        $history = AgentFollowUp::with(['agent', 'creator'])
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($agentFilter, fn($q) => $q->where('agent_id', $agentFilter))
            ->orderByDesc('created_at')
            ->paginate(40)
            ->withQueryString();

        $agents  = User::orderBy('name')->get(['id', 'name']);

        return $this->view('backoffice.crm.agent.follow-ups', [
            'history'      => $history,
            'agents'       => $agents,
            'statuses'     => AgentFollowUp::STATUSES,
            'statusFilter' => $statusFilter,
            'agentFilter'  => $agentFilter,
        ]);
    }

    // ── Save a call note / follow-up ──────────────────────────────────────────

    public function saveFollowUp(Request $request)
    {
        $validated = $request->validate([
            'crm_student_id'  => 'required|integer',
            'registration_id' => 'nullable|integer',
            'agent_id'        => 'nullable|integer|exists:users,id',
            'status'          => 'required|in:' . implode(',', array_keys(AgentFollowUp::STATUSES)),
            'note'            => 'nullable|string|max:2000',
            'follow_up_date'  => 'nullable|date',
        ]);

        AgentFollowUp::create([
            ...$validated,
            'called_at'  => now(),
            'created_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Note de suivi enregistrée.');
    }

    // ── Unpaid students ───────────────────────────────────────────────────────

    public function unpaid(Request $request)
    {
        $storeId = $this->currentStrStoreId();

        $rows = CrmCollectionRow::query()
            ->when($storeId, fn($q) => $q->where('crm_store_id', $storeId))
            ->where('rest_amount', '>', 0)
            ->orderByDesc('rest_amount')
            ->paginate(40)
            ->withQueryString();

        return $this->view('backoffice.crm.agent.unpaid', [
            'rows'    => $rows,
            'storeId' => $storeId,
        ]);
    }

    // ── New registrations without payment ────────────────────────────────────

    public function newWithoutPayment(Request $request)
    {
        $storeId = $this->currentStrStoreId();
        $since   = now()->subDays(30)->toDateString();

        // Registrations created in last 30 days
        $regStudentIds = CrmRegistration::query()
            ->when($storeId, fn($q) => $q->where('crm_store_id', $storeId))
            ->where('date_creation', '>=', $since)
            ->pluck('crm_student_id')
            ->unique()
            ->all();

        // Of those, who has an outstanding balance?
        $rows = CrmCollectionRow::query()
            ->whereIn('student_id', $regStudentIds)
            ->when($storeId, fn($q) => $q->where('crm_store_id', $storeId))
            ->where('rest_amount', '>', 0)
            ->orderByDesc('rest_amount')
            ->paginate(40)
            ->withQueryString();

        return $this->view('backoffice.crm.agent.new-without-payment', [
            'rows'    => $rows,
            'storeId' => $storeId,
            'since'   => $since,
        ]);
    }

    // ── Drill: student detail card (JSON for modal) ───────────────────────────

    public function studentCard(Request $request)
    {
        $studentId = (int) $request->query('crm_student_id');

        $score = CrmChurnScore::where('crm_student_id', $studentId)->first();
        $followUps = AgentFollowUp::with('agent')
            ->where('crm_student_id', $studentId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $collections = CrmCollectionRow::where('student_id', $studentId)
            ->where('rest_amount', '>', 0)
            ->get(['rest_amount', 'due_date', 'payment_delay_days', 'store_name']);

        return response()->json([
            'score'       => $score,
            'follow_ups'  => $followUps,
            'collections' => $collections,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildKpis(?int $storeId): array
    {
        $base = CrmChurnScore::query()->forStore($storeId);

        $recentlyContacted = AgentFollowUp::where('created_at', '>=', now()->subDays(7))
            ->pluck('crm_student_id')
            ->unique()
            ->all();

        return [
            'total'    => (clone $base)->count(),
            'critical' => (clone $base)->where('risk_level', 'critical')->count(),
            'high'     => (clone $base)->where('risk_level', 'high')->count(),
            'medium'   => (clone $base)->where('risk_level', 'medium')->count(),
            'low'      => (clone $base)->where('risk_level', 'low')->count(),
            'call_today' => (clone $base)
                ->whereIn('risk_level', ['high', 'critical'])
                ->whereNotIn('crm_student_id', $recentlyContacted)
                ->count(),
            'unpaid' => CrmCollectionRow::query()
                ->when($storeId, fn($q) => $q->where('crm_store_id', $storeId))
                ->where('rest_amount', '>', 0)
                ->distinct('student_id')
                ->count('student_id'),
            'follow_ups_today' => AgentFollowUp::dueToday()
                ->where('status', '!=', 'solved')
                ->count(),
        ];
    }
}
