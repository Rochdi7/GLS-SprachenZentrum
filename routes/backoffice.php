<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\ProfileController;
use App\Http\Controllers\Backoffice\BlogCategoryController;
use App\Http\Controllers\Backoffice\BlogPostController;
use App\Http\Controllers\Backoffice\SiteController;
use App\Http\Controllers\Backoffice\TeacherController;
use App\Http\Controllers\Backoffice\GroupController;
use App\Http\Controllers\Backoffice\CertificateController;

/*
|--------------------------------------------------------------------------
| BACKOFFICE ROUTES
|--------------------------------------------------------------------------
| These routes are already wrapped with:
| middleware(['web', 'auth']) in web.php
|--------------------------------------------------------------------------
*/

/* Dashboard */
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/* Optional dynamic pages (avoid profile conflict) */
Route::get('/dashboard/{routeName}/{name?}', 
    [DashboardController::class, 'pageView']
)->where('routeName', '^(?!profile).*$');


/*
|--------------------------------------------------------------------------
| BACKOFFICE MODULES
|--------------------------------------------------------------------------
*/
Route::prefix('backoffice')
    ->name('backoffice.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | BLOG → Catégories
        |--------------------------------------------------------------------------
        */
        Route::prefix('blog/categories')
            ->name('blog.categories.')
            ->group(function () {
                Route::get('/', [BlogCategoryController::class, 'index'])->name('index');
                Route::get('/create', [BlogCategoryController::class, 'create'])->name('create');
                Route::post('/', [BlogCategoryController::class, 'store'])->name('store');
                Route::get('/{category}/edit', [BlogCategoryController::class, 'edit'])->name('edit');
                Route::put('/{category}', [BlogCategoryController::class, 'update'])->name('update');
                Route::delete('/{category}', [BlogCategoryController::class, 'destroy'])->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | BLOG → Articles
        |--------------------------------------------------------------------------
        */
        Route::prefix('blog/posts')
            ->name('blog.posts.')
            ->group(function () {
                Route::get('/', [BlogPostController::class, 'index'])->name('index');
                Route::get('/create', [BlogPostController::class, 'create'])->name('create');
                Route::post('/', [BlogPostController::class, 'store'])->name('store');
                Route::get('/{post}/edit', [BlogPostController::class, 'edit'])->name('edit');
                Route::put('/{post}', [BlogPostController::class, 'update'])->name('update');
                Route::delete('/{post}', [BlogPostController::class, 'destroy'])->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | SITES GLS
        |--------------------------------------------------------------------------
        */
        Route::prefix('sites')
            ->name('sites.')
            ->group(function () {
                Route::get('/', [SiteController::class, 'index'])->name('index');
                Route::get('/create', [SiteController::class, 'create'])->name('create');
                Route::post('/', [SiteController::class, 'store'])->name('store');
                Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');
                Route::put('/{site}', [SiteController::class, 'update'])->name('update');
                Route::delete('/{site}', [SiteController::class, 'destroy'])->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | ENSEIGNANTS
        |--------------------------------------------------------------------------
        */
        Route::prefix('teachers')
            ->name('teachers.')
            ->group(function () {
                Route::get('/', [TeacherController::class, 'index'])->name('index');
                Route::get('/create', [TeacherController::class, 'create'])->name('create');
                Route::post('/', [TeacherController::class, 'store'])->name('store');
                Route::get('/{teacher}/edit', [TeacherController::class, 'edit'])->name('edit');
                Route::put('/{teacher}', [TeacherController::class, 'update'])->name('update');
                Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | GROUPES
        |--------------------------------------------------------------------------
        */
        Route::prefix('groups')
            ->name('groups.')
            ->group(function () {
                Route::get('/', [GroupController::class, 'index'])->name('index');
                Route::get('/create', [GroupController::class, 'create'])->name('create');
                Route::post('/', [GroupController::class, 'store'])->name('store');
                Route::get('/{group}/edit', [GroupController::class, 'edit'])->name('edit');
                Route::put('/{group}', [GroupController::class, 'update'])->name('update');
                Route::delete('/{group}', [GroupController::class, 'destroy'])->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | CERTIFICATS
        |--------------------------------------------------------------------------
        */
        Route::prefix('certificates')
            ->name('certificates.')
            ->group(function () {
                Route::get('/', [CertificateController::class, 'index'])->name('index');
                Route::get('/create', [CertificateController::class, 'create'])->name('create');
                Route::post('/', [CertificateController::class, 'store'])->name('store');
                Route::get('/{certificate}', [CertificateController::class, 'show'])->name('show');
                Route::get('/{certificate}/edit', [CertificateController::class, 'edit'])->name('edit');
                Route::put('/{certificate}', [CertificateController::class, 'update'])->name('update');
                Route::delete('/{certificate}', [CertificateController::class, 'destroy'])->name('destroy');

                // PDF EXPORT
                Route::get('/{certificate}/pdf', [CertificateController::class, 'pdf'])
                    ->name('pdf');
            });
    });


/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard')->group(function () {

    // Profile page
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

    // Update info
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Update password
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])
        ->name('profile.updatePassword');
});
