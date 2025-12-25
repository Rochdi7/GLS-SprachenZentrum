<?php

use Illuminate\Support\Facades\Route;
use Spatie\ResponseCache\Middlewares\CacheResponse;

use App\Http\Controllers\Frontoffice\HomeController;
use App\Http\Controllers\Frontoffice\PageController;
use App\Http\Controllers\Frontoffice\GlsController;
use App\Http\Controllers\Frontoffice\GroupController;
use App\Http\Controllers\Frontoffice\BlogController;
use App\Http\Controllers\Frontoffice\StudienkollegController;

/*
|--------------------------------------------------------------------------
| FRONT OFFICE – PUBLIC PAGES (CACHED)
|--------------------------------------------------------------------------
| Only GET requests
| Safe for SEO & performance
*/
Route::middleware(CacheResponse::class)->group(function () {

    // ============================
    // HOME
    // ============================
    Route::get('/', [HomeController::class, 'index'])
        ->name('front.home');

    // ============================
    // ABOUT
    // ============================
    Route::get('/about', [HomeController::class, 'about'])
        ->name('front.about');

    // ============================
    // FAQ + CONTACT (GET ONLY)
    // ============================
    Route::get('/faq', [PageController::class, 'faq'])
        ->name('front.faq');

    Route::get('/contact', [PageController::class, 'contact'])
        ->name('front.contact');

    // ============================
    // SITES
    // ============================
    Route::get('/sites/{slug}', [GroupController::class, 'show'])
        ->name('front.sites.show');

    // ============================
    // COURSES
    // ============================
    Route::get('/intensive-courses', [PageController::class, 'intensiveCourses'])
        ->name('front.intensive-courses');

    Route::get('/online-courses', [PageController::class, 'onlineCourses'])
        ->name('front.online-courses');

    Route::get('/pricing', [PageController::class, 'pricing'])
        ->name('front.pricing');

    // ============================
    // EXAMS
    // ============================
    Route::get('/exams/gls', [PageController::class, 'glsExams'])
        ->name('front.exams.gls');

    Route::get('/exams/osd', [PageController::class, 'osdExams'])
        ->name('front.exams.osd');

    // ============================
    // BLOG
    // ============================
    Route::get('/blog', [BlogController::class, 'index'])
        ->name('blog.index');

    Route::get('/blog/{slug}', [BlogController::class, 'details'])
        ->name('blog.show');

    // ============================
    // STUDENT STORIES
    // ============================
    Route::get('/student-stories', [PageController::class, 'studentStories'])
        ->name('front.student-stories');

    // ============================
    // CERTIFICATE CHECK (GET ONLY)
    // ============================
    Route::get('/certificate-check', [PageController::class, 'certificateCheck'])
        ->name('front.certificate.check');

    // ============================
    // NIVEAUX
    // ============================
    Route::get('/niveaux/a1', [PageController::class, 'niveauA1'])
        ->name('front.niveaux.a1');

    Route::get('/niveaux/a2', [PageController::class, 'niveauA2'])
        ->name('front.niveaux.a2');

    Route::get('/niveaux/b1', [PageController::class, 'niveauB1'])
        ->name('front.niveaux.b1');

    Route::get('/niveaux/b2', [PageController::class, 'niveauB2'])
        ->name('front.niveaux.b2');

    // ============================
    // STUDIENKOLLEGS
    // ============================
    Route::get('/studienkollegs', [StudienkollegController::class, 'index'])
        ->name('front.studienkollegs');

    Route::get('/studienkollegs/{slug}', [StudienkollegController::class, 'show'])
        ->name('front.studienkollegs.show');
});

/*
|--------------------------------------------------------------------------
| FRONT OFFICE – FORMS (NOT CACHED)
|--------------------------------------------------------------------------
*/
Route::post('/contact', [PageController::class, 'contactPost'])
    ->name('front.contact.post');

Route::post('/certificate-check', [PageController::class, 'certificateCheckPost'])
    ->name('front.certificate.check.post');

Route::post('/gls-inscription', [GlsController::class, 'store'])
    ->name('gls.inscription');
