<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Attestation;
use App\Models\AttestationRequest;
use App\Models\Certificate;
use App\Models\GlsInscription;
use App\Models\Group;
use App\Models\GroupApplication;
use App\Models\GroupLevelFollowup;
use App\Models\Site;
use App\Models\Teacher;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Centre-scoped dashboard for non-Super-Admin users.
 *
 *   • Admin (centre responsible) → management variant (dashboard.admin),
 *     limited to the centre(s) they are affected to — NOT global.
 *   • Réception / other staff → front-desk variant (dashboard.staff).
 *
 * Super Admin keeps the global DashboardController view.
 * Encaissements / Primes intentionally excluded for now (still in dev).
 */
class StaffDashboardController extends Controller
{
    use ScopesToUserSites;

    public function index(Request $request)
    {
        $user = $request->user();

        // Professors never see the staff dashboard — redirect to their own
        // read-only payroll history.
        if ($user->isProfessor()) {
            return redirect()->route('backoffice.payroll.professor.index');
        }

        $isAdmin = $user->hasRole('Admin');

        // Centre scoping. Super Admin sees all; Admin and others are scoped to
        // their assigned centres via the shared trait.
        $accessibleSites = $this->accessibleSites($user);
        $allowedSiteIds  = $this->accessibleSiteIds($user); // null = all (Super Admin only)

        // Hard gate: a centre-scoped user with NO centre can't see data.
        if (! $this->userSeesAllSites($user) && empty($allowedSiteIds)) {
            return view('backoffice.dashboard.staff_no_site');
        }

        // Optional centre picker (must be in the accessible list).
        $requested    = $request->filled('site_id') ? (int) $request->site_id : null;
        $activeSiteId = $this->resolveRequestedSiteId($requested, $user);

        $now        = Carbon::now();
        $weekStart  = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd    = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        // Effective centre filter. null = all centres (only non-admin see-all).
        $effectiveIds = $allowedSiteIds;
        if ($activeSiteId) {
            $effectiveIds = [$activeSiteId];
        }

        // Helper to apply the site filter (null = no filter).
        $applySite = function ($query, string $relation = null) use ($effectiveIds) {
            if ($effectiveIds === null) return $query;
            if (empty($effectiveIds)) return $query->whereRaw('1 = 0');
            return $relation
                ? $query->whereHas($relation, fn ($q) => $q->whereIn('site_id', $effectiveIds))
                : $query->whereIn('site_id', $effectiveIds);
        };

        // ── KPIs ────────────────────────────────────────────────────
        $certificatesTotal     = $applySite(Certificate::query())->count();
        $certificatesThisMonth = $applySite(Certificate::whereBetween('created_at', [$monthStart, $monthEnd]))->count();

        $attestationsTotal     = $applySite(Attestation::query(), 'group')->count();
        $attestationsThisMonth = $applySite(Attestation::whereBetween('created_at', [$monthStart, $monthEnd]), 'group')->count();

        // Attestation requests + translations — scoped where a centre link exists,
        // otherwise global (these public submissions are not centre-bound).
        $attestationRequestsTotal   = AttestationRequest::count();
        $attestationRequestsPending = AttestationRequest::where('status', AttestationRequest::STATUS_PENDING)->count();

        $translationsTotal  = Translation::count();
        $translationsActive = Translation::whereIn('status', [Translation::STATUS_PENDING, Translation::STATUS_TRANSLATOR])->count();

        $teachersTotal     = $applySite(Teacher::query())->count();
        $activeGroupsTotal = $applySite(Group::where('status', 'active'))->count();
        $groupsTotal       = $applySite(Group::query())->count();

        // Group applications (candidatures) — scoped via group → site.
        $groupAppsTotal   = $applySite(GroupApplication::query(), 'group')->count();
        $groupAppsPending = $applySite(GroupApplication::where('status', 'pending'), 'group')->count();

        // ── Suivi niveau (rappels profs) — due today or overdue ─────
        $followupsQuery = GroupLevelFollowup::with(['group.teacher', 'group.site'])
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', $now->toDateString())
            ->orderBy('due_date');
        $followupsQuery = $applySite($followupsQuery, 'group');

        $levelFollowupsDue = $followupsQuery->get()
            ->groupBy('group_id')
            ->map(fn ($items) => $items->sortBy('due_date')->first())
            ->values();

        // All follow-ups for those groups — powers the level-pill progression.
        $groupIds = $levelFollowupsDue->pluck('group_id')->unique()->values();
        $levelFollowupsByGroup = $groupIds->isNotEmpty()
            ? GroupLevelFollowup::whereIn('group_id', $groupIds)->get()->groupBy('group_id')
            : collect();

        // ── Personal weekly schedule (always the user's own) ────────
        $myWeek = UserSchedule::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date')
            ->get();
        $myWorkedMinutes = $myWeek->sum('worked_minutes');

        // ── Weekly reports for accessible centres (current week) ────
        $reportQuery = WeeklyReport::with(['teacher.site', 'group'])
            ->whereBetween('report_date', [$weekStart, $weekEnd]);
        $reportQuery = $applySite($reportQuery, 'teacher');
        $weeklyReports = $reportQuery->orderBy('report_date')->get();

        // ── Activity trend (last 6 months) ─────────────────────────
        // Attestation requests + translations received per month. These are
        // public submissions (not centre-bound), matching their KPI scope.
        $activityLabels = [];
        $activityRequests = [];
        $activityTranslations = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $from = $m->copy()->startOfMonth();
            $to   = $m->copy()->endOfMonth();
            $activityLabels[]       = ucfirst($m->locale('fr')->isoFormat('MMM'));
            $activityRequests[]     = AttestationRequest::whereBetween('created_at', [$from, $to])->count();
            $activityTranslations[] = Translation::whereBetween('created_at', [$from, $to])->count();
        }

        $payload = compact(
            'accessibleSites', 'activeSiteId',
            'certificatesTotal', 'certificatesThisMonth',
            'attestationsTotal', 'attestationsThisMonth',
            'attestationRequestsTotal', 'attestationRequestsPending',
            'translationsTotal', 'translationsActive',
            'teachersTotal', 'activeGroupsTotal', 'groupsTotal',
            'groupAppsTotal', 'groupAppsPending',
            'levelFollowupsDue', 'levelFollowupsByGroup',
            'myWeek', 'myWorkedMinutes',
            'weeklyReports',
            'monthStart', 'monthEnd', 'weekStart', 'weekEnd',
            'activityLabels', 'activityRequests', 'activityTranslations'
        );

        if ($isAdmin) {
            $payload = array_merge($payload, $this->adminExtras($user, $effectiveIds, $weekStart, $weekEnd, $now));
            return view('backoffice.dashboard.admin', $payload);
        }

        return view('backoffice.dashboard.staff', $payload);
    }

    /**
     * Extra management data shown only on the Admin (centre responsible) board.
     */
    private function adminExtras(User $user, ?array $effectiveIds, Carbon $weekStart, Carbon $weekEnd, Carbon $now): array
    {
        $scope = function ($query, string $relation = null) use ($effectiveIds) {
            if ($effectiveIds === null) return $query;
            if (empty($effectiveIds)) return $query->whereRaw('1 = 0');
            return $relation
                ? $query->whereHas($relation, fn ($q) => $q->whereIn('site_id', $effectiveIds))
                : $query->whereIn('site_id', $effectiveIds);
        };

        // Pending follow-ups (all upcoming, not just due) — pressure indicator.
        $pendingFollowups = $scope(
            GroupLevelFollowup::where('status', 'pending'),
            'group'
        )->count();

        // Weekly reports expected vs received: active groups should each have one.
        $activeGroups = $scope(Group::where('status', 'active'))->count();
        $reportsReceived = $scope(
            WeeklyReport::whereBetween('report_date', [$weekStart, $weekEnd]),
            'teacher'
        )->count();

        // Staff of this centre with a schedule this week (coverage).
        $staffCount = User::whereNotNull('staff_role')
            ->where('is_active', true)
            ->where(function ($q) use ($effectiveIds) {
                if ($effectiveIds === null) return;
                if (empty($effectiveIds)) { $q->whereRaw('1 = 0'); return; }
                $q->whereIn('site_id', $effectiveIds)
                  ->orWhereHas('sites', fn ($sq) => $sq->whereIn('sites.id', $effectiveIds));
            })
            ->count();

        $staffScheduledThisWeek = UserSchedule::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->when($effectiveIds !== null, fn ($q) => empty($effectiveIds)
                ? $q->whereRaw('1 = 0')
                : $q->whereIn('site_id', $effectiveIds))
            ->distinct('user_id')
            ->count('user_id');

        // Per-centre breakdown (only meaningful when managing >1 centre).
        $siteBreakdown = collect();
        $ids = $effectiveIds ?? ($user->accessibleSiteIds() ?: []);
        if (! empty($ids)) {
            $siteBreakdown = Site::whereIn('id', $ids)->orderBy('name')->get()->map(fn ($s) => [
                'name'    => $s->name,
                'groups'  => Group::where('site_id', $s->id)->where('status', 'active')->count(),
                'teachers'=> Teacher::where('site_id', $s->id)->count(),
            ]);
        }

        // ── Centre-scoped charts (last 12 months) ──────────────────
        $adminGroupsTrend       = $this->adminTrend('groups', 'site_id', $effectiveIds, 8);
        $adminTeachersTrend     = $this->adminTrend('teachers', 'site_id', $effectiveIds, 8);
        $adminCertsTrend        = $this->adminTrend('certificates', 'site_id', $effectiveIds, 8);

        $adminCertsByMonth      = $this->adminChartByMonth('certificates', 'site_id', $effectiveIds, 12);
        $adminGroupsByMonth     = $this->adminChartByMonth('groups', 'site_id', $effectiveIds, 12);
        $adminInscriptionsByMonth = $this->adminChartByMonth('gls_inscriptions', 'centre', $effectiveIds, 12);
        $adminGroupAppsByMonth  = $this->adminGroupAppsByMonth($effectiveIds, 12);

        $adminGroupAppsByStatus = [
            'En attente' => $scope(GroupApplication::where('status', 'pending'), 'group')->count(),
            'Approuvées' => $scope(GroupApplication::where('status', 'approved'), 'group')->count(),
            'Rejetées'   => $scope(GroupApplication::where('status', 'rejected'), 'group')->count(),
        ];

        return compact(
            'pendingFollowups', 'activeGroups', 'reportsReceived',
            'staffCount', 'staffScheduledThisWeek', 'siteBreakdown',
            'adminGroupsTrend', 'adminTeachersTrend', 'adminCertsTrend',
            'adminCertsByMonth', 'adminGroupsByMonth',
            'adminInscriptionsByMonth', 'adminGroupAppsByMonth',
            'adminGroupAppsByStatus'
        );
    }

    private function adminTrend(string $table, string $siteCol, ?array $ids, int $months = 8): array
    {
        $now  = Carbon::now();
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $from = $now->copy()->subMonths($i)->startOfMonth();
            $to   = $now->copy()->subMonths($i)->endOfMonth();
            $q    = DB::table($table)->whereBetween('created_at', [$from, $to]);
            if ($ids !== null) {
                if (empty($ids)) { $data[] = 0; continue; }
                $q->whereIn($siteCol, $ids);
            }
            $data[] = $q->count();
        }
        return $data ?: array_fill(0, $months, 0);
    }

    private function adminChartByMonth(string $table, string $siteCol, ?array $ids, int $months = 12): \Illuminate\Support\Collection
    {
        $now    = Carbon::now();
        $result = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $from  = $now->copy()->subMonths($i)->startOfMonth();
            $to    = $now->copy()->subMonths($i)->endOfMonth();
            $q     = DB::table($table)->whereBetween('created_at', [$from, $to]);
            if ($ids !== null) {
                if (empty($ids)) { $result->put($from->format('M Y'), 0); continue; }
                $q->whereIn($siteCol, $ids);
            }
            $result->put($from->format('M Y'), $q->count());
        }
        return $result;
    }

    private function adminGroupAppsByMonth(?array $ids, int $months = 12): \Illuminate\Support\Collection
    {
        $now    = Carbon::now();
        $result = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $from = $now->copy()->subMonths($i)->startOfMonth();
            $to   = $now->copy()->subMonths($i)->endOfMonth();
            $q    = GroupApplication::whereBetween('created_at', [$from, $to]);
            if ($ids !== null) {
                if (empty($ids)) { $result->put($from->format('M Y'), 0); continue; }
                $q->whereHas('group', fn ($gq) => $gq->whereIn('site_id', $ids));
            }
            $result->put($from->format('M Y'), $q->count());
        }
        return $result;
    }
}
