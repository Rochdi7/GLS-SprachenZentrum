<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\Api\GroupApiController;

/**
 * =============================
 * AUTH ROUTES (NO LOCALE)
 * =============================
 */
Auth::routes(['verify' => true]);

/**
 * =============================
 * MEDIA (NO LOCALE)
 * =============================
 */
Route::get('/media/{id}/{filename}', function ($id, $filename) {
    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);
    return response()->file($media->getPath());
})->name('media.custom');

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
    Route::get('/centers', function () {
        return \App\Models\Site::select('id','name','city')->get();
    });
});
