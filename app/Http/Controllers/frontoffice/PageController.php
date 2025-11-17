<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function faq()
    {
        return view('frontoffice.faq');
    }

    public function contact()
    {
        return view('frontoffice.contact');
    }

    public function sites()
    {
        return view('frontoffice.sites');
    }

    // ===========================
    // INDIVIDUAL CENTER PAGES
    // ===========================

    public function siteRabat()
    {
        return view('frontoffice.sites.rabat');
    }

    public function siteSale()
    {
        return view('frontoffice.sites.sale');
    }

    public function siteKenitra()
    {
        return view('frontoffice.sites.kenitra');
    }

    public function siteCasablanca()
    {
        return view('frontoffice.sites.casablanca');
    }

    public function siteAgadir()
    {
        return view('frontoffice.sites.agadir');
    }

    public function siteMarrakech()
    {
        return view('frontoffice.sites.marrakech');
    }

    // ===========================
    // COURSES
    // ===========================

    public function intensiveCourses()
    {
        return view('frontoffice.intensive-courses');
    }

    public function onlineCourses()
    {
        return view('frontoffice.online-courses');
    }
}
