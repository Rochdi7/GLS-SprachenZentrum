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

        // Get groups
        $groups = Group::with('teacher')
            ->where('site_id', $site->id)
            ->orderBy('status')
            ->orderBy('level')
            ->get()
            ->groupBy('period_label');

        // Return the site page dynamically
        return view('frontoffice.sites.' . $slug, compact('site', 'groups'));
    }
}
