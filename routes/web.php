<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
Route::middleware(['web'])->group(function () {

    Route::get('/media/{id}/{filename}', function ($id, $filename) {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);
        return response()->file($media->getPath());
    })->name('media.custom');

    // Auth routes
    Auth::routes(['verify' => true]);

    // Backoffice
    Route::middleware(['auth'])->group(function () {
        require __DIR__.'/backoffice.php';
    });

    // Frontoffice
    require __DIR__.'/frontoffice.php';
});

