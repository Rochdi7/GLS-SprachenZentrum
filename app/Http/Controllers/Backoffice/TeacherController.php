<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Teachers\StoreTeacherRequest;
use App\Http\Requests\Backoffice\Teachers\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\Site;

class TeacherController extends Controller
{
    use ScopesToUserSites;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = $this->scopeToUserSites(Teacher::with('site'))->latest()->get();

        return view('backoffice.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = $this->accessibleSites();
        return view('backoffice.teachers.create', compact('sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();
        [$primarySiteId, $siteIds] = $this->resolveSites($validated);

        $validated['site_id'] = $primarySiteId;
        unset($validated['site_ids']);

        $teacher = Teacher::create($validated);
        $teacher->sites()->sync($siteIds);

        // Save image with MediaLibrary
        if ($request->hasFile('image')) {
            $teacher->addMedia($request->file('image'))
                ->toMediaCollection('teacher_image');
        }

        return redirect()
            ->route('backoffice.teachers.index')
            ->with('success', 'L’enseignant a été ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $teacher = Teacher::with('site')->findOrFail($id);
        return view('backoffice.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $teacher = Teacher::with('sites')->findOrFail($id);

        // Active centres the current user can pick from...
        $sites = $this->accessibleSites();

        // ...plus any centre this teacher is already assigned to, even if it is
        // now inactive. Otherwise the assignment is invisible in the dropdown and
        // silently wiped on the next save.
        $assignedIds = $teacher->accessibleSiteIds();
        $missing = Site::whereIn('id', $assignedIds)
            ->whereNotIn('id', $sites->pluck('id'))
            ->get();

        $sites = $sites->concat($missing)->sortBy('name')->values();

        return view('backoffice.teachers.edit', compact('teacher', 'sites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, string $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validated = $request->validated();
        [$primarySiteId, $siteIds] = $this->resolveSites($validated);

        $validated['site_id'] = $primarySiteId;
        unset($validated['site_ids']);

        $teacher->update($validated);
        $teacher->sites()->sync($siteIds);

        // Replace image if new one uploaded
        if ($request->hasFile('image')) {
            $teacher->clearMediaCollection('teacher_image');

            $teacher->addMedia($request->file('image'))
                ->toMediaCollection('teacher_image');
        }

        return redirect()
            ->route('backoffice.teachers.index')
            ->with('success', 'L’enseignant a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $teacher = Teacher::findOrFail($id);

        // Delete media
        $teacher->clearMediaCollection('teacher_image');

        $teacher->delete();

        return redirect()
            ->route('backoffice.teachers.index')
            ->with('success', 'L’enseignant a été supprimé avec succès.');
    }

    /**
     * Resolve the multi-centre selection into a primary `site_id` and the full
     * list of affected site ids. The primary is the first selected centre; it
     * is kept on `teachers.site_id` so groups/attestations/CRM mirror sync
     * keep working, while the pivot lists every centre the teacher works in.
     *
     * @return array{0:int|null,1:array<int>}
     */
    private function resolveSites(array $validated): array
    {
        $ids = collect($validated['site_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values();

        $primary = $ids->isNotEmpty() ? (int) $ids->first() : null;

        return [$primary, $ids->all()];
    }
}
