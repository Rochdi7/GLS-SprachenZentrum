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
        return view('frontoffice.faq');
    }

    /**
     * Display the Contact page.
     */
    public function contact()
    {
        return view('frontoffice.contact');
    }

    /**
     * Display the Sites page.
     */
    public function sites()
    {
        return view('frontoffice.sites');
    }

    /**
     * Display the Intensive Courses page.
     */
    public function intensiveCourses()
    {
        return view('frontoffice.intensive-courses');
    }

    /**
     * Display the Online Courses page. 
     */
    public function onlineCourses()
    {
        return view('frontoffice.online-courses');
    }
}
