<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Sites\StoreSiteRequest;
use App\Http\Requests\Backoffice\Sites\UpdateSiteRequest;
use App\Models\Site;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::latest()->paginate(10);
        return view('backoffice.sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backoffice.sites.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteRequest $request)
    {
        $site = Site::create($request->validated());

        // Hero photo (MediaLibrary)
        if ($request->hasFile('hero_image')) {
            $site->addMedia($request->file('hero_image'))
                ->toMediaCollection('hero_image');
        }

        return redirect()
            ->route('backoffice.sites.index')
            ->with('success', 'Le site a été créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $site = Site::findOrFail($id);
        return view('backoffice.sites.show', compact('site'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $site = Site::findOrFail($id);
        return view('backoffice.sites.edit', compact('site'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiteRequest $request, string $id)
    {
        $site = Site::findOrFail($id);

        $site->update($request->validated());

        // Update hero image
        if ($request->hasFile('hero_image')) {
            $site->clearMediaCollection('hero_image');
            $site->addMedia($request->file('hero_image'))
                ->toMediaCollection('hero_image');
        }

        return redirect()
            ->route('backoffice.sites.index')
            ->with('success', 'Le site a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $site = Site::findOrFail($id);

        // Delete media
        $site->clearMediaCollection('hero_image');

        $site->delete();

        return redirect()
            ->route('backoffice.sites.index')
            ->with('success', 'Le site a été supprimé avec succès.');
    }
}
