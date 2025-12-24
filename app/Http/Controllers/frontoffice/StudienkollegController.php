<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Studienkolleg;
use Illuminate\View\View;

class StudienkollegController extends Controller
{
    /**
     * Studienkollegs listing
     */
    public function index(): View
    {
        $featured = Studienkolleg::query()
            ->where('public', true)
            ->where('featured', true)
            ->latest()
            ->first();

        $studienkollegs = Studienkolleg::query()
            ->where('public', true)
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->latest()
            ->paginate(12);

        return view('frontoffice.studienkollegs.index', [
            'featured'       => $featured,
            'studienkollegs' => $studienkollegs,
        ]);
    }

    /**
     * Studienkolleg detail page
     */
    public function show(string $slug): View
    {
        $studienkolleg = Studienkolleg::query()
            ->where('slug', $slug)
            ->where('public', true)
            ->firstOrFail();

        return view('frontoffice.studienkollegs.show', [
            'studienkolleg' => $studienkolleg,
        ]);
    }
}
