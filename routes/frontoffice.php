<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontoffice\HomeController;
use App\Http\Controllers\Frontoffice\PageController;

Route::middleware(['web'])->group(function () {

    // ============================
    // HOME
    // ============================
    Route::get('/', [HomeController::class, 'index'])->name('front.home');

    // ============================
    // ABOUT
    // ============================
    Route::get('/about', [HomeController::class, 'about'])->name('front.about');

    // ============================
    // FAQ + CONTACT
    // ============================
    Route::get('/faq', [PageController::class, 'faq'])->name('front.faq');
    Route::get('/contact', [PageController::class, 'contact'])->name('front.contact');

    // ============================
    // SITES (Centers)
    // ============================
    Route::get('/sites', [PageController::class, 'sites'])->name('front.sites');

    // Individual center pages
    Route::get('/sites/rabat', [PageController::class, 'siteRabat'])->name('front.sites.rabat');
    Route::get('/sites/sale', [PageController::class, 'siteSale'])->name('front.sites.sale');
    Route::get('/sites/kenitra', [PageController::class, 'siteKenitra'])->name('front.sites.kenitra');
    Route::get('/sites/casablanca', [PageController::class, 'siteCasablanca'])->name('front.sites.casablanca');
    Route::get('/sites/agadir', [PageController::class, 'siteAgadir'])->name('front.sites.agadir');
    Route::get('/sites/marrakech', [PageController::class, 'siteMarrakech'])->name('front.sites.marrakech');

    // ============================
    // COURSES
    // ============================
    Route::get('/intensive-courses', [PageController::class, 'intensiveCourses'])->name('front.intensive-courses');
    Route::get('/online-courses', [PageController::class, 'onlineCourses'])->name('front.online-courses');
    Route::get('/pricing', [PageController::class, 'pricing'])->name('front.pricing');

    // ============================
    // EXAMS
    // ============================
    Route::get('/exams/gls', [PageController::class, 'glsExams'])->name('front.exams.gls');
    Route::get('/exams/osd', [PageController::class, 'osdExams'])->name('front.exams.osd');

    // ============================
    // RESOURCES
    // ============================
    Route::get('/blog', [PageController::class, 'blog'])->name('front.blog');
    Route::get('/student-stories', [PageController::class, 'studentStories'])->name('front.student-stories');
});
