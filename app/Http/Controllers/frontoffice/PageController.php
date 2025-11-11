<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    /**
     * Display the FAQ page.
     */
    public function faq()
    {
        // Loads resources/views/frontoffice/faq.blade.php
        return view('frontoffice.faq');
    }
    public function contact()
    {
        // Loads resources/views/frontoffice/faq.blade.php
        return view('frontoffice.contact');
    }
}
