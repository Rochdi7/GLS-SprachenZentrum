<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hikvision API Mirror
|--------------------------------------------------------------------------
| Loaded from routes/backoffice.php inside the backoffice route group.
| URLs resolve under /backoffice/hikvision and names under
| backoffice.hikvision.*.
|
*/

Route::prefix('hikvision')
    ->name('hikvision.')
    ->middleware('permission:hikvision.view')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Backoffice\Hikvision\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::prefix('devices')->name('devices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backoffice\Hikvision\DeviceController::class, 'index'])->name('index');
            Route::get('/{device}', [\App\Http\Controllers\Backoffice\Hikvision\DeviceController::class, 'show'])->name('show');
        });

        Route::prefix('persons')->name('persons.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backoffice\Hikvision\PersonController::class, 'index'])->name('index');
            Route::get('/{person}', [\App\Http\Controllers\Backoffice\Hikvision\PersonController::class, 'show'])->name('show');
        });

        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backoffice\Hikvision\AttendanceController::class, 'index'])->name('index');
            Route::get('/{attendance}', [\App\Http\Controllers\Backoffice\Hikvision\AttendanceController::class, 'show'])->name('show');
        });

        Route::get('/alarms', [\App\Http\Controllers\Backoffice\Hikvision\AlarmController::class, 'index'])
            ->name('alarms.index');
        Route::get('/webhooks', [\App\Http\Controllers\Backoffice\Hikvision\WebhookController::class, 'index'])
            ->name('webhooks.index');
        Route::get('/settings', [\App\Http\Controllers\Backoffice\Hikvision\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::get('/logs', [\App\Http\Controllers\Backoffice\Hikvision\LogController::class, 'index'])
            ->name('logs.index');
    });
