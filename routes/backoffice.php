<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\ProfileController;

/*
|--------------------------------------------------------------------------
| BACKOFFICE ROUTES
|--------------------------------------------------------------------------
| These routes are already wrapped with:
| - middleware(['web', 'auth']) in web.php
| So do NOT add middleware again here.
*/

/* Dashboard */
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

/* Optional dynamic pages example: /dashboard/settings */
Route::get('/dashboard/{routeName}/{name?}', [DashboardController::class, 'pageView'])
    ->where('routeName', '^(?!profile).*$'); // avoid conflict with /dashboard/profile

/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('dashboard')->group(function () {

    // Profile page
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile.index');

    // Update general profile info (name, bio, phone, address, photo…)
    Route::post('/profile/update', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Update password
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])
        ->name('profile.updatePassword');
});

