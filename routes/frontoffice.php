<?php

use Illuminate\Support\Facades\Route;
use Spatie\ResponseCache\Middlewares\CacheResponse;

use App\Http\Controllers\Frontoffice\HomeController;
use App\Http\Controllers\Frontoffice\PageController;
use App\Http\Controllers\Frontoffice\GlsController;
use App\Http\Controllers\Frontoffice\GroupController;
use App\Http\Controllers\Frontoffice\BlogController;
use App\Http\Controllers\Frontoffice\StudienkollegController;
use App\Http\Controllers\Frontoffice\ConsultationController;
use App\Http\Controllers\Frontoffice\NewsletterController;
use App\Http\Controllers\Frontoffice\GroupApplicationController;
use App\Http\Controllers\Frontoffice\LevelQuizController;
use App\Http\Controllers\Frontoffice\AttestationRequestController;
use App\Http\Controllers\Frontoffice\FeedbackController;
use App\Http\Controllers\Frontoffice\TranslationTrackingController;

Route::middleware(CacheResponse::class)->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('front.home');
    Route::get('/about', [HomeController::class, 'about'])->name('front.about');

    Route::get('/faq', [PageController::class, 'faq'])->name('front.faq');
    Route::get('/contact', [PageController::class, 'contact'])->name('front.contact');

    Route::get('/sites/online', function () {
        return redirect()->route('front.online-courses');
    });

    Route::get('/sites/gls-online', function () {
        return redirect()->route('front.online-courses');
    });

    Route::get('/sites/{slug}', [GroupController::class, 'show'])->name('front.sites.show');

    Route::get('/intensive-courses', [PageController::class, 'intensiveCourses'])->name('front.intensive-courses');
    Route::get('/online-courses', [PageController::class, 'onlineCourses'])->name('front.online-courses');

    Route::get('/pricing', [PageController::class, 'pricing'])->name('front.pricing');

    Route::get('/online-registration', [PageController::class, 'onlineRegistration'])->name('front.online-registration');
    Route::get('/gls-inscription', [PageController::class, 'glsInscription'])->name('front.gls-inscription');
    Route::get('/gls-inscription/success', [PageController::class, 'glsSuccess'])->name('front.gls-inscription.success');

    Route::get('/demande-attestation', [AttestationRequestController::class, 'create'])->name('front.attestation-request.create');
    Route::get('/demande-attestation/success', [AttestationRequestController::class, 'success'])->name('front.attestation-request.success');

    Route::get('/feedback', [FeedbackController::class, 'create'])->name('front.feedback.create');
    Route::get('/feedback/success', [FeedbackController::class, 'success'])->name('front.feedback.success');

    Route::get('/exams/gls', [PageController::class, 'glsExams'])->name('front.exams.gls');
    Route::get('/exams/osd', [PageController::class, 'osdExams'])->name('front.exams.osd');
    Route::get('/exams/goethe', [PageController::class, 'goetheExams'])->name('front.exams.goethe');

    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'details'])->name('blog.show');

    Route::get('/student-stories', [PageController::class, 'studentStories'])->name('front.student-stories');

    Route::get('/certificate-check', [PageController::class, 'certificateCheck'])->name('front.certificate.check');

    Route::get('/niveaux/a1', [PageController::class, 'niveauA1'])->name('front.niveaux.a1');
    Route::get('/niveaux/a2', [PageController::class, 'niveauA2'])->name('front.niveaux.a2');
    Route::get('/niveaux/b1', [PageController::class, 'niveauB1'])->name('front.niveaux.b1');
    Route::get('/niveaux/b2', [PageController::class, 'niveauB2'])->name('front.niveaux.b2');
    Route::get('/niveaux/c1', [PageController::class, 'niveauC1'])->name('front.niveaux.c1');

    Route::get('/studienkollegs', [StudienkollegController::class, 'index'])->name('front.studienkollegs');
    Route::get('/studienkollegs/{slug}', [StudienkollegController::class, 'show'])->name('front.studienkollegs.show');

    // ✅ LANDING STATIC PAGE (content + buttons)
    Route::get('/discover-your-level', [PageController::class, 'discoverYourLevel'])
        ->name('front.discover-your-level');

    Route::get('/terms', [PageController::class, 'terms'])->name('front.terms');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('front.privacy');
    Route::get('/partners/fc-marokko', [PageController::class, 'fcMarokko'])->name('front.partners.fc_marokko');
});

/*
|--------------------------------------------------------------------------
| AD LANDING PAGES (Meta & Google) - NOT cached so tracking fires fresh
|--------------------------------------------------------------------------
*/
Route::get('/lp/meta', [PageController::class, 'lpMeta'])->name('front.lp.meta');
Route::get('/lp/google', [PageController::class, 'lpGoogle'])->name('front.lp.google');
Route::get('/lp/tiktok', [PageController::class, 'lpTiktok'])->name('front.lp.tiktok');

/*
|--------------------------------------------------------------------------
| QUIZ PAGE (dynamic) - better NOT cached
|--------------------------------------------------------------------------
| This renders resources/views/frontoffice/quiz/index.blade.php
*/
Route::get('/discover-your-level/quiz', [LevelQuizController::class, 'showQuiz'])
    ->name('front.discover-your-level.quiz');

// Optional submit route later (if you use it)
Route::post('/discover-your-level/answer', [LevelQuizController::class, 'answer'])
    ->name('front.discover-your-level.answer');

/*
|--------------------------------------------------------------------------
| TRADUCTIONS - public tracking by CIN (NOT cached)
|--------------------------------------------------------------------------
*/
Route::get('/traductions/suivi', [TranslationTrackingController::class, 'show'])
    ->middleware('throttle:public-lookup')
    ->name('front.translations.track');

/*
|--------------------------------------------------------------------------
| POST routes — rate limited (public-form: 10 req/min per IP) to protect
| against spam/abuse now that submissions trigger emails + CRM/Sheets sync.
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:public-form')->group(function () {
    Route::post('/contact', [PageController::class, 'contactPost'])->name('front.contact.post');
    Route::post('/certificate-check', [PageController::class, 'certificateCheckPost'])->name('front.certificate.check.post');
    Route::post('/online-registration', [PageController::class, 'storeOnlineRegistration'])->name('front.online-registration.store');
    Route::post('/gls-inscription', [GlsController::class, 'store'])->name('gls.inscription');
    Route::post('/demande-attestation', [AttestationRequestController::class, 'store'])->name('front.attestation-request.store');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('front.feedback.store');
    Route::post('/consultation', [ConsultationController::class, 'store'])->name('front.consultation.store');
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::post('/groups/apply', [GroupApplicationController::class, 'storeFromQuery'])->name('front.groups.apply');
    Route::post('/groups/{group}/apply', [GroupApplicationController::class, 'store'])->name('front.groups.apply.legacy');
});
