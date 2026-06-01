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

Route::get('/debug-crm-api-centers', function () {
    $crm = app(\App\Services\Crm\Crm::class);
    // Use raw client to hit the lov endpoint directly
    try {
        $resp = $crm->client()->get('/api/external/v1/lov/sites', ['limit' => 100]);
        return [
            'success' => true,
            'crm_api_url' => config('services.crm.base_url'),
            'centers_from_crm' => $resp,
        ];
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ];
    }
})->middleware('auth');

Route::get('/debug-crm-token', function () {
    $crm = app(\App\Services\Crm\Crm::class);
    try {
        $resp = $crm->client()->get('/api/external/v1/lov/banks', ['limit' => 1]);
        return [
            'success' => true,
            'token_first_chars' => substr(config('services.crm.token'), 0, 8) . '...',
            'can_fetch_banks' => true,
            'response' => $resp,
        ];
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
})->middleware('auth');

Route::get('/debug-crm-raw-data', function () {
    $crm = app(\App\Services\Crm\Crm::class);
    try {
        // Scan many classes to find all unique Store IDs
        $resp = $crm->client()->get('/api/external/v1/groups/classes', ['page' => 0, 'size' => 500]);
        $data = $resp['data'] ?? [];
        $stores = [];

        foreach ($data as $class) {
            $sid = $class['STR_STORE_ID'] ?? 'MISSING';
            if (!isset($stores[$sid])) {
                $stores[$sid] = [
                    'store_id' => $sid,
                    'example_class' => $class['NAME'],
                    'teacher' => $class['EMPLOYEE_TEACHER_FULL_NAME'] ?? 'N/A',
                ];
            }
        }

        return [
            'success' => true,
            'discovered_stores' => array_values($stores),
            'total_scanned' => count($data)
        ];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
})->middleware('auth');

/**
 * =============================
 * Sitemap route (handles Content-Type header)
 * =============================
 */
Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path, [
        'Content-Type' => 'application/xml; charset=utf-8'
    ]);
})->name('sitemap');

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
