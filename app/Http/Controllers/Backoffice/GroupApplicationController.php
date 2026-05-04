<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Jobs\SyncLeadToGoogleSheetJob;
use App\Models\Group;
use App\Models\GroupApplication;
use App\Models\Site;
use Illuminate\Http\Request;

class GroupApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = GroupApplication::with(['group.site'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('center')) {
            $query->whereHas('group.site', function ($q) use ($request) {
                $q->where('id', $request->center);
            });
        }

        $applications = $query->get();
        $sites = Site::orderBy('name')->get();

        return view('backoffice.applications.index', compact('applications', 'sites'));
    }

    public function show(GroupApplication $application)
    {
        $application->load(['group.site']);

        return view('backoffice.applications.show', compact('application'));
    }

    public function create()
    {
        $groups = Group::with('site')->orderBy('name')->get();

        return view('backoffice.applications.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:50'],
            'birthday' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
        ]);

        $status = $validated['status'] ?? 'pending';
        unset($validated['status']);

        $application = new GroupApplication($validated);
        $application->setStatus($status);
        $application->save();

        return redirect()
            ->route('backoffice.applications.index')
            ->with('success', 'Application créée avec succès.');
    }

    public function edit(GroupApplication $application)
    {
        $groups = Group::with('site')->orderBy('name')->get();

        return view('backoffice.applications.edit', compact('application', 'groups'));
    }

    public function update(Request $request, GroupApplication $application)
    {
        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:50'],
            'birthday' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $status = $validated['status'];
        unset($validated['status']);

        $application->fill($validated);
        $application->setStatus($status);
        $application->save();

        return redirect()
            ->route('backoffice.applications.index')
            ->with('success', 'Application mise à jour avec succès.');
    }

    public function destroy(GroupApplication $application)
    {
        $application->delete();

        return redirect()
            ->route('backoffice.applications.index')
            ->with('success', 'Application supprimée avec succès.');
    }

    public function resync(GroupApplication $application)
    {
        SyncLeadToGoogleSheetJob::dispatch($application);

        return back()->with('success', "Re-synchronisation lancée pour l'application #{$application->id}.");
    }
}
