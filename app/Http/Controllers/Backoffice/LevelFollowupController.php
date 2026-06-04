<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupLevelFollowup;
use App\Models\Site;
use App\Models\Teacher;
use App\Services\LevelFollowupGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LevelFollowupController extends Controller
{
    private function scopeToUserSites($query)
    {
        $user = auth()->user();
        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        $siteIds = $user->accessibleSiteIds();

        return $query->whereHas('group', function ($q) use ($siteIds) {
            $q->whereIn('site_id', $siteIds);
        });
    }

    public function index(Request $request)
    {
        $now  = Carbon::now();
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        $query = GroupLevelFollowup::query()
            ->with(['group.teacher', 'group.site'])
            ->orderBy('status')
            ->orderBy('due_date');

        $this->scopeToUserSites($query);

        if ($request->filled('center')) {
            $query->whereHas('group.site', function ($siteQuery) use ($request) {
                $siteQuery->where('id', $request->center);
            });
        }

        if ($request->filled('teacher')) {
            $query->whereHas('group.teacher', function ($teacherQuery) use ($request) {
                $teacherQuery->where('id', $request->teacher);
            });
        }

        $followups = $query->get();

        // Only show sites/teachers the user can access in filters
        $accessibleSiteIds = $isSuperAdmin ? null : $user->accessibleSiteIds();
        $sitesQuery = Site::query()->orderBy('name');
        if (!$isSuperAdmin) {
            $sitesQuery->whereIn('id', $accessibleSiteIds);
        }
        $sites = $sitesQuery->get();

        $teachersQuery = Teacher::query()->orderBy('name');
        if (!$isSuperAdmin) {
            $teachersQuery->whereHas('groups', fn ($q) => $q->whereIn('site_id', $accessibleSiteIds));
        }
        $teachers = $teachersQuery->get();

        $rows = $followups
            ->groupBy('group_id')
            ->map(function ($items) use ($now) {
                $items = $items->sortBy('level_start_date')->values();

                $current = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date || !$f->level_end_date) {
                        return false;
                    }

                    $start = Carbon::parse($f->level_start_date)->startOfDay();
                    $end = Carbon::parse($f->level_end_date)->startOfDay();

                    return $now->betweenIncluded($start, $end);
                });

                if ($current) {
                    return $current;
                }

                $future = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date) {
                        return false;
                    }

                    return Carbon::parse($f->level_start_date)->startOfDay()->gt($now);
                });

                if ($future) {
                    return $future;
                }

                return $items->last();
            })
            ->filter()
            ->values();

        $dueRows = $rows->filter(function ($f) use ($now) {
            return ($f->status === 'pending')
                && $f->due_date
                && Carbon::parse($f->due_date)->lte($now);
        });

        return view('backoffice.level_followups.index', [
            'followups' => $rows,
            'dueFollowups' => $dueRows,
            'levelFollowupsByGroup' => $followups->groupBy('group_id'),
            'sites' => $sites,
            'teachers' => $teachers,
            'now' => $now,
        ]);
    }

    public function showGroup(Group $group)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->canAccessSite($group->site_id),
            403
        );

        $now = Carbon::now();
        $group->loadMissing(['teacher', 'site']);
        $followups = $this->getGroupFollowups($group);

        return view('backoffice.level_followups.show', [
            'group' => $group,
            'followups' => $followups,
            'now' => $now,
        ]);
    }

    public function pdf()
    {
        $now = Carbon::now();

        $query = GroupLevelFollowup::query()
            ->with(['group.teacher'])
            ->orderBy('status')
            ->orderBy('due_date');

        $this->scopeToUserSites($query);

        $followups = $query->get();

        $rows = $followups
            ->groupBy('group_id')
            ->map(function ($items) use ($now) {
                $items = $items->sortBy('level_start_date')->values();

                $current = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date || !$f->level_end_date) {
                        return false;
                    }

                    $start = Carbon::parse($f->level_start_date)->startOfDay();
                    $end = Carbon::parse($f->level_end_date)->startOfDay();

                    return $now->betweenIncluded($start, $end);
                });

                if ($current) {
                    return $current;
                }

                $future = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date) {
                        return false;
                    }

                    return Carbon::parse($f->level_start_date)->startOfDay()->gt($now);
                });

                if ($future) {
                    return $future;
                }

                return $items->last();
            })
            ->filter()
            ->values();

        $pdf = Pdf::loadView('backoffice.level_followups.pdf', [
                'rows' => $rows,
                'levelFollowupsByGroup' => $followups->groupBy('group_id'),
                'now' => $now,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('suivi-niveau-' . $now->format('Y-m-d') . '.pdf');
    }

    public function pdfByGroup(Group $group)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->canAccessSite($group->site_id),
            403
        );

        $now = Carbon::now();
        $group->loadMissing(['teacher', 'site']);
        $groupFollowups = $this->getGroupFollowups($group);

        $pdf = Pdf::loadView('backoffice.level_followups.pdf_group', [
                'group' => $group,
                'followups' => $groupFollowups,
                'now' => $now,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '-', (string) ($group->name ?? ('group-' . $group->id)));

        return $pdf->download('suivi-niveau-' . $safeName . '-' . $now->format('Y-m-d') . '.pdf');
    }

    public function complete(GroupLevelFollowup $followup, Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->canAccessSite($followup->group?->site_id),
            403
        );

        $validated = $request->validate([
            'done_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $followup->update([
            'status' => 'done',
            'done_at' => now(),
            'done_notes' => $validated['done_notes'] ?? null,
        ]);

        // Recalculate subsequent levels so the next one starts the day after done_at
        if ($followup->group) {
            (new LevelFollowupGenerator())->generateForGroup($followup->group);
        }

        return back()->with('success', "Niveau {$followup->level} marque comme termine.");
    }

    public function updateNotes(GroupLevelFollowup $followup, Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->canAccessSite($followup->group?->site_id),
            403
        );

        $validated = $request->validate([
            'done_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $followup->update([
            'done_notes' => $validated['done_notes'] ?? null,
        ]);

        return back()->with('success', "Note du niveau {$followup->level} enregistree.");
    }


    public function destroy(GroupLevelFollowup $followup)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->canAccessSite($followup->group?->site_id),
            403
        );

        $level = $followup->level;

        $followup->delete();

        return back()->with('success', "Suivi niveau {$level} supprime.");
    }

    public function syncAll()
    {
        $user = auth()->user();
        $generator = new LevelFollowupGenerator();

        if ($user->hasRole('Super Admin')) {
            $generator->generateAllActive();
            $msg = 'Suivi niveau synchronise pour tous les groupes actifs.';
        } else {
            $siteIds = $user->accessibleSiteIds();
            Group::query()
                ->where('status', 'active')
                ->whereNotNull('date_debut')
                ->whereIn('site_id', $siteIds)
                ->chunkById(200, function ($groups) use ($generator) {
                    foreach ($groups as $group) {
                        $generator->generateForGroup($group);
                    }
                });
            $msg = 'Suivi niveau synchronise pour les groupes de votre centre.';
        }

        return back()->with('success', $msg);
    }

    private function getGroupFollowups(Group $group)
    {
        return GroupLevelFollowup::query()
            ->with(['group.teacher', 'group.site'])
            ->where('group_id', $group->id)
            ->orderBy('level_start_date')
            ->get();
    }
}
