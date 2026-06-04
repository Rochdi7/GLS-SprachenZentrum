<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Attestation;
use App\Models\AttestationRequest;
use App\Models\Certificate;
use App\Models\Group;
use App\Models\GroupLevelFollowup;
use App\Models\Teacher;
use App\Models\Translation;
use App\Models\UserSchedule;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Dashboard for staff users (Réception / Coordination / Manager / etc.) —
 * scoped to the centres they are affected to. Super Admin / Admin keep using
 * the full DashboardController.
 *
 * Encaissements / Primes intentionally excluded for now (still in dev).
 */
class StaffDashboardController extends Controller
{
    use ScopesToUserSites;

    public function index(Request $request)
    {
        $user = $request->user();
        $accessibleSites = $this->accessibleSites($user);
        $allowedSiteIds = $this->accessibleSiteIds($user); // null = all centres

        // Hard gate: non-admin users with NO centre assignment can't see data.
        if (! $this->userSeesAllSites($user) && empty($allowedSiteIds)) {
            return view('backoffice.dashboard.staff_no_site');
        }

        // Optional centre picker (must be in accessible list)
        $requested = $request->filled('site_id') ? (int) $request->site_id : null;
        $activeSiteId = $this->resolveRequestedSiteId($requested, $user);

        $now = Carbon::now();
        $weekStart  = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd    = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        // Effective filter — if a specific centre is picked, narrow to it.
        $effectiveIds = $allowedSiteIds; // null = all
        if ($activeSiteId) {
            $effectiveIds = [$activeSiteId];
        }

        // ── KPIs ────────────────────────────────────────────────────
        $certQuery = Certificate::query();
        $certThisMonthQuery = Certificate::whereBetween('created_at', [$monthStart, $monthEnd]);
        if ($effectiveIds !== null) {
            if (empty($effectiveIds)) {
                $certQuery->whereRaw('1 = 0');
                $certThisMonthQuery->whereRaw('1 = 0');
            } else {
                $certQuery->whereIn('site_id', $effectiveIds);
                $certThisMonthQuery->whereIn('site_id', $effectiveIds);
            }
        }
        $certificatesTotal     = $certQuery->count();
        $certificatesThisMonth = $certThisMonthQuery->count();

        // Attestations: tied via group → site_id
        $attestationsQuery = Attestation::query();
        $attestationsThisMonthQuery = Attestation::whereBetween('created_at', [$monthStart, $monthEnd]);
        if ($effectiveIds !== null) {
            if (empty($effectiveIds)) {
                $attestationsQuery->whereRaw('1 = 0');
                $attestationsThisMonthQuery->whereRaw('1 = 0');
            } else {
                $attestationsQuery->whereHas('group', fn ($q) => $q->whereIn('site_id', $effectiveIds));
                $attestationsThisMonthQuery->whereHas('group', fn ($q) => $q->whereIn('site_id', $effectiveIds));
            }
        }
        $attestationsTotal     = $attestationsQuery->count();
        $attestationsThisMonth = $attestationsThisMonthQuery->count();

        // Attestation requests (public form submissions)
        $attestationRequestsTotal   = AttestationRequest::count();
        $attestationRequestsPending = AttestationRequest::where('status', AttestationRequest::STATUS_PENDING)->count();

        // Translations (Maroc–Allemagne tracking)
        $translationsTotal    = Translation::count();
        $translationsActive   = Translation::whereIn('status', [Translation::STATUS_PENDING, Translation::STATUS_TRANSLATOR])->count();

        // Teachers (scoped to accessible centres)
        $teachersQuery = Teacher::query();
        if ($effectiveIds !== null) {
            $teachersQuery->whereIn('site_id', $effectiveIds ?: [0]);
        }
        $teachersTotal = $teachersQuery->count();

        // Active groups (scoped)
        $activeGroupsQuery = Group::where('status', 'active');
        if ($effectiveIds !== null) {
            $activeGroupsQuery->whereIn('site_id', $effectiveIds ?: [0]);
        }
        $activeGroupsTotal = $activeGroupsQuery->count();

        // ── Suivi niveau (rappels profs) ───────────────────────────
        $followupsQuery = GroupLevelFollowup::with(['group.teacher', 'group.site'])
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', $now->toDateString())
            ->orderBy('due_date');

        if ($effectiveIds !== null) {
            if (empty($effectiveIds)) {
                $followupsQuery->whereRaw('1 = 0');
            } else {
                $followupsQuery->whereHas('group', fn ($q) => $q->whereIn('site_id', $effectiveIds));
            }
        }

        $levelFollowupsDue = $followupsQuery->get()
            // de-duplicate: keep earliest due followup per group
            ->groupBy('group_id')
            ->map(fn ($items) => $items->sortBy('due_date')->first())
            ->values();

        // ── Personal weekly schedule ───────────────────────────────
        $myWeek = UserSchedule::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date')
            ->get();
        $myWorkedMinutes = $myWeek->sum('worked_minutes');

        // ── Weekly reports for accessible centres (current week) ──
        $reportQuery = WeeklyReport::with(['teacher.site', 'group'])
            ->whereBetween('report_date', [$weekStart, $weekEnd]);
        if ($effectiveIds !== null) {
            if (empty($effectiveIds)) {
                $reportQuery->whereRaw('1 = 0');
            } else {
                $reportQuery->whereHas('teacher', fn ($q) => $q->whereIn('site_id', $effectiveIds));
            }
        }
        $weeklyReports = $reportQuery->orderBy('report_date')->get();

        return view('backoffice.dashboard.staff', compact(
            'accessibleSites', 'activeSiteId',
            'certificatesTotal', 'certificatesThisMonth',
            'attestationsTotal', 'attestationsThisMonth',
            'attestationRequestsTotal', 'attestationRequestsPending',
            'translationsTotal', 'translationsActive',
            'teachersTotal', 'activeGroupsTotal',
            'levelFollowupsDue',
            'myWeek', 'myWorkedMinutes',
            'weeklyReports',
            'monthStart', 'monthEnd', 'weekStart', 'weekEnd'
        ));
    }
}
