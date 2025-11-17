<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontoffice\HomeController;
use App\Http\Controllers\Frontoffice\PageController;

Route::middleware(['web'])->group(function () {

    // Home Page
    Route::get('/', [HomeController::class, 'index'])->name('front.home');

    // About Page
    Route::get('/about', [HomeController::class, 'about'])->name('front.about');

    // FAQ Page
    Route::get('/faq', [PageController::class, 'faq'])->name('front.faq');

    // Contact Page
    Route::get('/contact', [PageController::class, 'contact'])->name('front.contact');

    // All Sites Page (main centers listing)
    Route::get('/sites', [PageController::class, 'sites'])->name('front.sites');

    // --- Individual Center Pages ---
    Route::get('/sites/rabat', [PageController::class, 'siteRabat'])->name('front.sites.rabat');
    Route::get('/sites/sale', [PageController::class, 'siteSale'])->name('front.sites.sale');
    Route::get('/sites/kenitra', [PageController::class, 'siteKenitra'])->name('front.sites.kenitra');
    Route::get('/sites/casablanca', [PageController::class, 'siteCasablanca'])->name('front.sites.casablanca');
    Route::get('/sites/agadir', [PageController::class, 'siteAgadir'])->name('front.sites.agadir');
    Route::get('/sites/marrakech', [PageController::class, 'siteMarrakech'])->name('front.sites.marrakech');

    // Courses Pages
    Route::get('/intensive-courses', [PageController::class, 'intensiveCourses'])->name('front.intensive-courses');
    Route::get('/online-courses', [PageController::class, 'onlineCourses'])->name('front.online-courses');
});
