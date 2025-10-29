<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Group everything under `web` middleware (for CSRF/session/auth)
Route::middleware(['web'])->group(function () {
    // Auth routes (login, register, password reset)
    Auth::routes();

    // Backoffice routes – protected
    Route::middleware(['auth'])->group(function () {
        require __DIR__.'/backoffice.php';
    });

    // Frontoffice routes – public
    require __DIR__.'/frontoffice.php';
});
