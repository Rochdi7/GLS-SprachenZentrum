<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Models\UserSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Planning (RH) — unified on User. Two audiences:
 *   • A logged-in user sees & edits THEIR OWN weekly planning.
 *   • A center admin (Super Admin / Admin / Manager on the same site) sees
 *     and can edit the planning of any user on their site.
 *
 * Super Admin sees all sites.
 */
class ScheduleController extends Controller
{
    // ─── Legacy tabular view (admin-only overview) ──────────────────────

    public function index(Request $request)
    {
        $authUser = $request->user();
        $isAdmin = $authUser->isCenterAdmin();

        $sites = Site::where('is_active', true)->get();
        $roles = User::STAFF_ROLES;
        $staffQuery = User::query()->whereNotNull('staff_role')->where('is_active', true);

        if (! $authUser->hasRole('Super Admin')) {
            // Regular admin: limited to centres they are affected to (primary + extras)
            $accessibleSiteIds = $authUser->accessibleSiteIds();
            $staffQuery->where(function ($q) use ($accessibleSiteIds) {
                $q->whereIn('site_id', $accessibleSiteIds)
                  ->orWhereHas('sites', fn ($sq) => $sq->whereIn('sites.id', $accessibleSiteIds));
            });
        }
        $staff = $staffQuery->orderBy('name')->get();

        $query = UserSchedule::with(['user', 'site']);

        // Non-admins can only see their own schedule
        if (! $isAdmin) {
            $query->where('user_id', $authUser->id);
        } elseif (! $authUser->hasRole('Super Admin')) {
            $query->whereIn('site_id', $authUser->accessibleSiteIds());
        }

        if ($isAdmin) {
            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            }
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('role')) {
                $query->whereHas('user', fn($q) => $q->where('staff_role', $request->role));
            }
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if (! $request->filled('date_from') && ! $request->filled('date_to')) {
            $query->where('date', '>=', now()->startOfMonth()->toDateString())
                  ->where('date', '<=', now()->endOfMonth()->toDateString());
        }

        $schedules = $query->orderBy('date')->orderBy('start_time')->get();

        $totalWorked = $schedules->sum('worked_minutes');
        $totalBreak = $schedules->sum('break_minutes');
        $totalSpan = $schedules->sum('total_span_minutes');
        $employeeCount = $schedules->pluck('user_id')->unique()->count();

        $employeeTotals = $schedules->groupBy('user_id')->map(fn($group) => [
            'employee' => $group->first()->user,
            'days' => $group->count(),
            'worked_minutes' => $group->sum('worked_minutes'),
            'break_minutes' => $group->sum('break_minutes'),
        ])->sortByDesc('worked_minutes');

        // Aliases for existing blade files: pass as both $employees & $staff
        $employees = $staff;

        return view('backoffice.schedules.index', compact(
            'sites', 'employees', 'staff', 'roles', 'schedules',
            'totalWorked', 'totalBreak', 'totalSpan', 'employeeCount', 'employeeTotals',
            'isAdmin'
        ));
    }

    // ─── Weekly self-service / admin-on-site planning ───────────────────

    public function week(Request $request)
    {
        $authUser = $request->user();
        $isAdmin = $authUser->isCenterAdmin();

        // Which week?
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = (clone $weekStart)->endOfWeek(Carbon::SUNDAY);

        // Which user?
        $targetUserId = (int) ($request->user_id ?? $authUser->id);
        if (! $isAdmin) {
            $targetUserId = $authUser->id; // non-admins are locked to themselves
        }
        $target = User::findOrFail($targetUserId);

        $this->authorizeManage($authUser, $target);

        // Staff list for the admin's dropdown (scoped to their site unless Super Admin)
        $staffOptions = collect();
        if ($isAdmin) {
            $q = User::whereNotNull('staff_role')->where('is_active', true);
            if (! $authUser->hasRole('Super Admin')) {
                $accessibleSiteIds = $authUser->accessibleSiteIds();
                $q->where(function ($qq) use ($accessibleSiteIds) {
                    $qq->whereIn('site_id', $accessibleSiteIds)
                       ->orWhereHas('sites', fn ($sq) => $sq->whereIn('sites.id', $accessibleSiteIds));
                });
            }
            $staffOptions = $q->orderBy('name')->get();
        }

        $schedules = UserSchedule::where('user_id', $target->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn($s) => $s->date->format('Y-m-d'));

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $d = (clone $weekStart)->addDays($i);
            $key = $d->format('Y-m-d');
            $days[] = [
                'date'     => $d,
                'key'      => $key,
                'label'    => $d->locale('fr')->isoFormat('ddd DD/MM'),
                'schedule' => $schedules->get($key),
            ];
        }

        $totalWorked = $schedules->sum('worked_minutes');

        return view('backoffice.schedules.week', compact(
            'authUser', 'target', 'isAdmin', 'staffOptions',
            'weekStart', 'weekEnd', 'days', 'totalWorked'
        ));
    }

    /**
     * Export the weekly planning as a PDF.
     *
     * Open to any authenticated user for their own week. Center admins can
     * export the week of any user they are allowed to manage (same site).
     */
    public function weekPdf(Request $request)
    {
        $authUser = $request->user();
        $isAdmin = $authUser->isCenterAdmin();

        $weekStart = $request->filled('week')
            ? Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = (clone $weekStart)->endOfWeek(Carbon::SUNDAY);

        $targetUserId = (int) ($request->user_id ?? $authUser->id);
        if (! $isAdmin) {
            $targetUserId = $authUser->id;
        }
        $target = User::findOrFail($targetUserId);
        $this->authorizeManage($authUser, $target);

        $schedules = UserSchedule::where('user_id', $target->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date')
            ->get();

        $data = [
            'employee'    => $target,
            'site'        => $target->site,
            'schedules'   => $schedules,
            'weekStart'   => $weekStart,
            'weekEnd'     => $weekEnd,
            'dateFrom'    => $weekStart->toDateString(),
            'dateTo'      => $weekEnd->toDateString(),
            'totalWorked' => $schedules->sum('worked_minutes'),
            'totalBreak'  => $schedules->sum('break_minutes'),
        ];

        $pdf = Pdf::loadView('backoffice.pdf.planning-week', $data)->setPaper('A4', 'landscape');
        $filename = 'planning_semaine_' . str_replace(' ', '_', $target->name) . '_' . $weekStart->toDateString() . '.pdf';

        return $pdf->download($filename);
    }

    public function saveWeek(Request $request)
    {
        $authUser = $request->user();
        $targetUserId = (int) ($request->user_id ?? $authUser->id);

        if (! $authUser->isCenterAdmin()) {
            $targetUserId = $authUser->id;
        }
        $target = User::findOrFail($targetUserId);
        $this->authorizeManage($authUser, $target);

        $request->validate([
            'week_start' => 'required|date',
            'days'       => 'required|array',
            'days.*.date'        => 'required|date',
            'days.*.start_time'  => 'nullable|date_format:H:i',
            'days.*.end_time'    => 'nullable|date_format:H:i',
            'days.*.break_start' => 'nullable|date_format:H:i',
            'days.*.break_end'   => 'nullable|date_format:H:i',
            'days.*.notes'       => 'nullable|string|max:500',
        ]);

        $saved = 0;
        $deleted = 0;

        foreach ($request->days as $day) {
            $date = $day['date'];
            $start = $day['start_time'] ?? null;
            $end = $day['end_time'] ?? null;

            // Empty row → delete existing entry for that day
            if (empty($start) || empty($end)) {
                $deleted += UserSchedule::where('user_id', $target->id)->where('date', $date)->delete();
                continue;
            }

            // Coherence: break needs both or neither
            $bs = $day['break_start'] ?? null;
            $be = $day['break_end'] ?? null;
            if (($bs && ! $be) || (! $bs && $be)) {
                return back()->withInput()->withErrors([
                    'break' => "Pause incomplète pour le {$date}.",
                ]);
            }
            if ($end <= $start) {
                return back()->withInput()->withErrors([
                    'end_time' => "L'heure de fin doit être après l'heure de début ({$date}).",
                ]);
            }

            $calc = UserSchedule::calculateMinutes([
                'start_time'  => $start,
                'end_time'    => $end,
                'break_start' => $bs,
                'break_end'   => $be,
            ]);

            UserSchedule::updateOrCreate(
                ['user_id' => $target->id, 'date' => $date],
                [
                    'site_id'            => $target->site_id,
                    'start_time'         => $start,
                    'end_time'           => $end,
                    'break_start'        => $bs,
                    'break_end'          => $be,
                    'total_span_minutes' => $calc['total_span_minutes'],
                    'break_minutes'      => $calc['break_minutes'],
                    'worked_minutes'     => $calc['worked_minutes'],
                    'notes'              => $day['notes'] ?? null,
                ]
            );
            $saved++;
        }

        return redirect()->route('backoffice.schedules.week', [
            'user_id' => $target->id,
            'week'    => $request->week_start,
        ])->with('success', "Planning enregistré ({$saved} jour(s), {$deleted} supprimé(s)).");
    }

    // ─── Legacy batch create (kept for admins) ──────────────────────────

    public function create(Request $request)
    {
        $this->authorizeAdmin($request->user());

        $sites = Site::where('is_active', true)->get();
        $employees = User::whereNotNull('staff_role')
            ->where('is_active', true)
            ->with('site')
            ->orderBy('name')
            ->get();

        return view('backoffice.schedules.create', compact('sites', 'employees'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'user_id'    => 'nullable|exists:users,id',
            'employee_id' => 'nullable|exists:users,id', // BC
            'date_from'  => 'required|date',
            'date_to'    => 'required|date|after_or_equal:date_from',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'break_end'   => 'nullable|date_format:H:i|after:break_start|before_or_equal:end_time',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $targetId = $validated['user_id'] ?? $validated['employee_id'] ?? null;
        if (! $targetId) {
            return back()->withInput()->withErrors(['user_id' => 'Utilisateur requis.']);
        }
        $target = User::findOrFail($targetId);
        $this->authorizeManage($request->user(), $target);

        if ((! empty($validated['break_start']) && empty($validated['break_end'])) ||
            (empty($validated['break_start']) && ! empty($validated['break_end']))) {
            return back()->withInput()->withErrors(['break_start' => 'Les deux heures de pause doivent être renseignées ou laissées vides.']);
        }

        $calc = UserSchedule::calculateMinutes($validated);

        $current = new \DateTime($validated['date_from']);
        $end = new \DateTime($validated['date_to']);
        $created = 0;
        $skipped = 0;

        while ($current <= $end) {
            $dow = (int) $current->format('w');
            if ($dow !== 0 && $dow !== 6) {
                $dateStr = $current->format('Y-m-d');
                $exists = UserSchedule::where('user_id', $target->id)->where('date', $dateStr)->exists();
                if (! $exists) {
                    UserSchedule::create([
                        'user_id'            => $target->id,
                        'site_id'            => $target->site_id,
                        'date'               => $dateStr,
                        'start_time'         => $validated['start_time'],
                        'end_time'           => $validated['end_time'],
                        'break_start'        => $validated['break_start'] ?? null,
                        'break_end'          => $validated['break_end'] ?? null,
                        'total_span_minutes' => $calc['total_span_minutes'],
                        'break_minutes'      => $calc['break_minutes'],
                        'worked_minutes'     => $calc['worked_minutes'],
                        'notes'              => $validated['notes'] ?? null,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }
            $current->modify('+1 day');
        }

        $msg = "{$created} jour(s) planifié(s).";
        if ($skipped > 0) $msg .= " {$skipped} ignoré(s) (déjà planifié).";

        return redirect()->route('backoffice.schedules.index')->with('success', $msg);
    }

    public function edit(UserSchedule $schedule, Request $request)
    {
        $this->authorizeManage($request->user(), $schedule->user);

        $sites = Site::where('is_active', true)->get();
        $employees = User::whereNotNull('staff_role')->where('is_active', true)->orderBy('name')->get();

        return view('backoffice.schedules.edit', compact('schedule', 'sites', 'employees'));
    }

    public function update(Request $request, UserSchedule $schedule)
    {
        $this->authorizeManage($request->user(), $schedule->user);

        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'break_end'   => 'nullable|date_format:H:i|after:break_start|before_or_equal:end_time',
            'notes'       => 'nullable|string|max:1000',
        ]);

        if ((! empty($validated['break_start']) && empty($validated['break_end'])) ||
            (empty($validated['break_start']) && ! empty($validated['break_end']))) {
            return back()->withInput()->withErrors(['break_start' => 'Les deux heures de pause doivent être renseignées ou laissées vides.']);
        }

        $target = User::findOrFail($validated['user_id']);
        $this->authorizeManage($request->user(), $target);
        $validated['site_id'] = $target->site_id;

        $validated = array_merge($validated, UserSchedule::calculateMinutes($validated));
        $schedule->update($validated);

        return redirect()->route('backoffice.schedules.index')->with('success', 'Planning mis à jour.');
    }

    public function destroy(UserSchedule $schedule, Request $request)
    {
        $this->authorizeManage($request->user(), $schedule->user);
        $schedule->delete();
        return redirect()->back()->with('success', 'Entrée supprimée.');
    }

    // ─── Authorization helpers ──────────────────────────────────────────

    private function authorizeAdmin(User $user): void
    {
        abort_unless($user->isCenterAdmin(), 403, 'Accès réservé aux responsables de centre.');
    }

    private function authorizeManage(User $auth, User $target): void
    {
        if ($auth->id === $target->id) return;               // self
        if ($auth->hasRole('Super Admin')) return;           // super
        if ($auth->isCenterAdmin()) {
            // Manager can manage any user that shares at least one centre with them.
            $shared = array_intersect($auth->accessibleSiteIds(), $target->accessibleSiteIds());
            if (! empty($shared)) return;
        }
        abort(403, 'Vous ne pouvez pas gérer le planning de cet utilisateur.');
    }
}
