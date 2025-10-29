<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontoffice\HomeController; // <-- StudlyCase
use App\Http\Middleware\SetLocaleFromUrl;

// Localized public site
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'en|de'],
    'middleware' => ['web', SetLocaleFromUrl::class],
], function () {
    Route::get('/', [HomeController::class, 'index'])->name('front.home');
});

// Redirect root (/) to /en
Route::get('/', fn () => redirect('/en'));
