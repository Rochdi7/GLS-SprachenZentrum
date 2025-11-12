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

    /**
     * Display the Contact page.
     */
    public function contact()
    {
        // Loads resources/views/frontoffice/contact.blade.php
        return view('frontoffice.contact');
    }

    /**
     * Display the Sites page.
     */
    public function sites()
    {
        // Loads resources/views/frontoffice/sites.blade.php
        return view('frontoffice.sites');
    }

    /**
     * Display the Intensive Courses page.
     */
    public function intensiveCourses()
    {
        // Loads resources/views/frontoffice/intensive-courses.blade.php
        return view('frontoffice.intensive-courses');
    }
}
