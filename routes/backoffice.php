<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\backoffice\DashboardController;

// Dashboard homepage (LightAble UI)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Optional: dynamic admin pages (like /dashboard/settings)
Route::get('/dashboard/{routeName}/{name?}', [DashboardController::class, 'pageView']);
