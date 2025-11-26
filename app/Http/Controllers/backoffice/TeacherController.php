<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Teachers\StoreTeacherRequest;
use App\Http\Requests\Backoffice\Teachers\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\Site;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::with('site')->latest()->paginate(10);

        return view('backoffice.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = Site::orderBy('name')->get(); // Needed for select site
        return view('backoffice.teachers.create', compact('sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $teacher = Teacher::create($request->validated());

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
        $teacher = Teacher::findOrFail($id);
        $sites = Site::orderBy('name')->get();

        return view('backoffice.teachers.edit', compact('teacher', 'sites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, string $id)
    {
        $teacher = Teacher::findOrFail($id);

        $teacher->update($request->validated());

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
}
