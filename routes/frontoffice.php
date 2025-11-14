<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontoffice\HomeController;
use App\Http\Controllers\Frontoffice\PageController;

// ===============================
//  FRONT OFFICE – Public Routes
//  Language: English only (for now)
// ===============================

Route::middleware(['web'])->group(function () {

    // Home Page
    Route::get('/', [HomeController::class, 'index'])->name('front.home');

    // About Page
    Route::get('/about', [HomeController::class, 'about'])->name('front.about');

    // FAQ Page
    Route::get('/faq', [PageController::class, 'faq'])->name('front.faq');

    // Contact Page
    Route::get('/contact', [PageController::class, 'contact'])->name('front.contact');

    // Sites Page
    Route::get('/sites', [PageController::class, 'sites'])->name('front.sites');

    // Intensive Courses Page
    Route::get('/intensive-courses', [PageController::class, 'intensiveCourses'])
        ->name('front.intensive-courses');

    // Online Courses Page  ✅ NEW
    Route::get('/online-courses', [PageController::class, 'onlineCourses'])
        ->name('front.online-courses');
});
