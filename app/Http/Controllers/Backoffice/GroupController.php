<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Groups\StoreGroupRequest;
use App\Http\Requests\Backoffice\Groups\UpdateGroupRequest;
use App\Models\Group;
use App\Models\Site;
use App\Models\Teacher;
use App\Services\LevelFollowupGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\GroupApplication;

class GroupController extends Controller
{
    use ScopesToUserSites;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Group::with(['site', 'teacher'])->latest();
        $this->scopeToUserSites($query);
        $groups = $query->get();

        return view('backoffice.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = $this->accessibleSites();
        $teachers = Teacher::orderBy('name')->get();

        return view('backoffice.groups.create', compact('sites', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request, LevelFollowupGenerator $generator)
    {
        $group = Group::create($request->validated());

        // Auto-generate suivi niveau followups right after creation (no manual command needed)
        $generator->generateForGroup($group);

        return redirect()->route('backoffice.groups.index')->with('success', 'Le groupe a été créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $group = Group::with(['site', 'teacher'])->findOrFail($id);

        return view('backoffice.groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $group = Group::findOrFail($id);

        // Non-admins can only edit groups in their accessible centres
        abort_unless(
            $this->userSeesAllSites() ||
            in_array((int) $group->site_id, auth()->user()->accessibleSiteIds(), true),
            403
        );

        $sites = $this->accessibleSites();
        $teachers = Teacher::orderBy('name')->get();

        return view('backoffice.groups.edit', compact('group', 'sites', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, string $id, LevelFollowupGenerator $generator)
    {
        $group = Group::findOrFail($id);
        $group->update($request->validated());

        // Auto-refresh followups when dates/level/teacher change
        $generator->generateForGroup($group);

        return redirect()->route('backoffice.groups.index')->with('success', 'Le groupe a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $group = Group::findOrFail($id);
        $group->delete();

        return redirect()->route('backoffice.groups.index')->with('success', 'Le groupe a été supprimé avec succès.');
    }

    /**
     * ✅ NEW: Page backoffice to manage "applied students" for a group
     * Route: backoffice.groups.applications
     */
    public function applications(Group $group, Request $request)
    {
        // 1) If you already have a relationship on Group like: applications()
        if (method_exists($group, 'applications')) {
            $applications = $group->applications()->latest()->get();

            return view('backoffice.groups.applications', compact('group', 'applications'));
        }

        // 2) Fallback: try to detect common tables for applications
        //    (so your page works even before you create the relationship)
        $candidateTables = ['group_applications', 'applications', 'group_registrations', 'registrations', 'inscriptions', 'applies'];

        $foundTable = null;
        foreach ($candidateTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'group_id')) {
                $foundTable = $table;
                break;
            }
        }

        if (!$foundTable) {
            // No table found -> show empty list
            $applications = collect();
            return view('backoffice.groups.applications', compact('group', 'applications'))->with('warning', "Aucune table d'inscriptions trouvée. Crée une table (ex: group_applications) avec group_id.");
        }

        // Minimal query on detected table
        $applications = DB::table($foundTable)->where('group_id', $group->id)->orderByDesc('id')->get();

        return view('backoffice.groups.applications', compact('group', 'applications'))->with('info', "Source: table `$foundTable` (fallback).");
    }

    /**
     * Used for frontoffice date-picker: get all weekdays between date_debut and date_fin
     */
    public function getDates($site_id, $level)
    {
        $groups = Group::where('site_id', $site_id)
            ->where('level', $level)
            ->get(['date_debut', 'date_fin']);

        $days = [];

        foreach ($groups as $group) {
            if ($group->date_debut && $group->date_fin) {
                $start = \Carbon\Carbon::parse($group->date_debut);
                $end = \Carbon\Carbon::parse($group->date_fin);

                while ($start->lte($end)) {
                    if (!$start->isWeekend()) {
                        $days[] = $start->format('Y-m-d');
                    }
                    $start->addDay();
                }
            }
        }

        return response()->json($days);
    }

    public function approve(Group $group, GroupApplication $application)
    {
        // security: ensure application belongs to group
        abort_unless($application->group_id == $group->id, 404);

        $application->update(['status' => 'approved']);

        return back()->with('success', 'Inscription approuvée.');
    }

    public function reject(Group $group, GroupApplication $application)
    {
        abort_unless($application->group_id == $group->id, 404);

        $application->update(['status' => 'rejected']);

        return back()->with('success', 'Inscription refusée.');
    }
}
