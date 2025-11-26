<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Groups\StoreGroupRequest;
use App\Http\Requests\Backoffice\Groups\UpdateGroupRequest;
use App\Models\Group;
use App\Models\Site;
use App\Models\Teacher;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Load site and teacher relationships
        $groups = Group::with(['site', 'teacher'])
                        ->latest()
                        ->paginate(10);

        return view('backoffice.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = Site::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();

        return view('backoffice.groups.create', compact('sites', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        Group::create($request->validated());

        return redirect()
            ->route('backoffice.groups.index')
            ->with('success', 'Le groupe a été créé avec succès.');
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

        $sites = Site::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();

        return view('backoffice.groups.edit', compact('group', 'sites', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, string $id)
    {
        $group = Group::findOrFail($id);

        $group->update($request->validated());

        return redirect()
            ->route('backoffice.groups.index')
            ->with('success', 'Le groupe a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $group = Group::findOrFail($id);

        $group->delete();

        return redirect()
            ->route('backoffice.groups.index')
            ->with('success', 'Le groupe a été supprimé avec succès.');
    }
}
