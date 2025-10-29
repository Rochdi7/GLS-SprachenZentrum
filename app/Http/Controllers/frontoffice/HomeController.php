<?php

namespace App\Http\Controllers\frontoffice;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('frontoffice.home'); // or return a string temporarily for testing
    }
}
