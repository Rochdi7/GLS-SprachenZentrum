<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // make sure this file exists:
        // resources/views/backoffice/index.blade.php
        return view('backoffice.index');
    }

    public function pageView($routeName, $name = null)
    {
        // this will try to load resources/views/backoffice/pages/{routeName}.blade.php
        return view("backoffice.pages.$routeName", ['name' => $name]);
    }
}
