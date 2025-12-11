<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        // Load all active GLS centers
        $sites = \App\Models\Site::where('is_active', true)->get();

        return view('frontoffice.home', compact('sites'));
    }

    /**
     * Display the About page.
     */
    public function about()
    {
        // Loads resources/views/frontoffice/about.blade.php
        return view('frontoffice.about');
    }
    public function FAQ()
    {
        return view('frontoffice.faq');
    }
}
