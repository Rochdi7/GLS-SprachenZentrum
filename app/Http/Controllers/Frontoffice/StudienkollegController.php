<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Studienkolleg;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudienkollegController extends Controller
{
    /**
     * Studienkollegs listing with filters
     */
    public function index(Request $request): View
    {
        // Get filter inputs from query string
        $filters = $request->only([
            'q', 'city', 'lang', 'course', 'german_level',
            'uni_assist', 'entrance_exam',
            'certification_required', 'translation_required',
            'featured',
        ]);

        // Clean up empty/null filters (keep '0' for uni_assist)
        $filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        // Build option lists for the filter UI
        $allCourses = ['T', 'M', 'W', 'G', 'S', 'TI', 'WW', 'SW'];

        // Featured only if no filters applied
        $featured = null;

        if (empty($filters)) {
            $featured = Studienkolleg::query()
                ->where('public', true)
                ->where('featured', true)
                ->orderByDesc('updated_at')
                ->first();
        }

        // Build query with filters
        $query = Studienkolleg::query()
            ->where('public', true)
            ->filter($filters)
            ->orderByDesc('featured')
            ->orderBy('name', 'asc');

        // Remove featured from the listing to avoid duplicates
        if ($featured) {
            $query->where('id', '!=', $featured->id);
        }

        // ✅ Paginate results (IMPORTANT)
        $studienkollegs = $query
            ->paginate(12)          // change 12 to whatever you want
            ->withQueryString();    // keeps filters in pagination links

        return view('frontoffice.studienkollegs.index', [
            'featured'       => $featured,
            'studienkollegs' => $studienkollegs,
            'allCourses'     => $allCourses,
            'filters'        => $filters,
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
