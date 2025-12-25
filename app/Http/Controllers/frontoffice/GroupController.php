<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Group;

class GroupController extends Controller
{
    public function show($slug)
    {
        // Get site
        $site = Site::where('slug', $slug)->firstOrFail();

        // Extract view name from slug
        // gls-marrakech → marrakech
        $view = str_replace('gls-', '', $slug);

        // Get groups
        $groups = Group::with('teacher')
            ->where('site_id', $site->id)
            ->orderBy('status')
            ->orderBy('level')
            ->get()
            ->groupBy('period_label');

        // Check if view exists (security)
        if (!view()->exists("frontoffice.sites.$view")) {
            abort(404);
        }

        return view("frontoffice.sites.$view", compact('site', 'groups'));
    }
}
