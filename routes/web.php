<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\CertificatePublicController;

Auth::routes([
    'verify' => true,
    'login'  => false,
]);

use App\Http\Controllers\Auth\LoginController;

Route::middleware('guest')->group(function () {
    Route::get('/gls-portal', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/gls-portal', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/**
 * =============================
 * MEDIA (NO LOCALE)
 * =============================
 *
 * Public-facing media handler. Only collections explicitly listed below are
 * served without authentication. Anything else (e.g. ID card scans on
 * GroupApplication) requires an authenticated backoffice user. The supplied
 * filename must match the stored file_name to prevent ID enumeration paired
 * with arbitrary filename guessing.
 */
Route::get('/media/{id}/{filename}', function ($id, $filename) {
    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);

    if (!hash_equals((string) $media->file_name, (string) $filename)) {
        abort(404);
    }

    $publicCollections = [
        'profile_photo',
        'teacher_photo',
        'site_hero',
        'site_logo',
        'blog_image',
        'blog_cover',
        'certificate_image',
        'studienkolleg_image',
        'quiz_image',
        'default',
    ];

    if (!in_array($media->collection_name, $publicCollections, true)) {
        if (!Auth::check()) {
            abort(403);
        }
    }

    return response()->file($media->getPath());
})
    ->where('filename', '[A-Za-z0-9._\-]+')
    ->name('media.custom');

/**
 * =============================
 * BACKOFFICE (NO LOCALE)
 * =============================
 */
Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/backoffice.php';
});

/**
 * =============================
 * FRONT-END LOCALIZED (FR/EN)
 * =============================
 */
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [
        'localize',
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath',
    ],
], function () {

    require __DIR__ . '/frontoffice.php';
});

/**
 * =============================
 * API ROUTES FOR FRONTEND AJAX
 * =============================
 * (NOT using api.php because user JS calls /api/... directly)
 */
Route::prefix('api')->group(function () {
    Route::get('/groups/dates/{site_id}/{level}', [GroupApiController::class, 'getDates']);
});

Route::get('/certificates/download/{token}', [CertificatePublicController::class, 'download'])
    ->name('certificates.public.download');

/**
 * =============================
 * TEST ERROR PAGES (REMOVE IN PRODUCTION)
 * =============================
 */
Route::prefix('test-errors')->group(function () {
    Route::get('/401', fn() => response()->view('errors.401', [], 401));
    Route::get('/403', fn() => response()->view('errors.403', [], 403));
    Route::get('/404', fn() => response()->view('errors.404', [], 404));
    Route::get('/419', fn() => response()->view('errors.419', [], 419));
    Route::get('/429', fn() => response()->view('errors.429', [], 429));
    Route::get('/500', fn() => response()->view('errors.500', [], 500));
    Route::get('/501', fn() => response()->view('errors.501', [], 501));
    Route::get('/502', fn() => response()->view('errors.502', [], 502));
    Route::get('/503', fn() => response()->view('errors.503', [], 503));
});
