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
        // Loads resources/views/frontoffice/home.blade.php
        return view('frontoffice.home');
    }

    /**
     * Display the About page.
     */
    public function about()
    {
        // Loads resources/views/frontoffice/about.blade.php
        return view('frontoffice.about');
    }
}
