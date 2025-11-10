<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontoffice\HomeController;

// ===============================
//  FRONT OFFICE – Public Routes
//  Language: English only (for now)
// ===============================

// All public pages
Route::middleware(['web'])->group(function () {
    
    // Home Page
    Route::get('/', [HomeController::class, 'index'])->name('front.home');
    
    // About Page
    Route::get('/about', [HomeController::class, 'about'])->name('front.about');

    
});
