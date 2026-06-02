<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM (Homeschool External API) — READ-ONLY proxy pages
|--------------------------------------------------------------------------
| Loaded from routes/backoffice.php inside the
|   Route::prefix('backoffice')->name('backoffice.')
| group, so all URLs resolve under /backoffice/crm/* and route names
| under backoffice.crm.*. Kept fully separated from the normal CRUD
| controllers in backoffice.php.
*/

Route::prefix('crm')
    ->name('crm.')
    ->middleware('permission:crm.view')
    ->group(function () {
        // Overview, center switch, lov, stats, duplicates, type-ahead.
        Route::controller(\App\Http\Controllers\Backoffice\Crm\CrmController::class)->group(function () {
            Route::get('/',                       'index')->name('index');
            Route::get('/stats',                  'stats')->name('stats');
            Route::get('/duplicates',             'duplicates')->name('duplicates');
            Route::post('/center',                'setCenter')->name('set-center');
            Route::get('/api/students-search',    'studentsSearch')->name('api.students-search');
            Route::get('/lov/{kind}',             'lov')->name('lov')
                ->where('kind', '[a-z0-9\-]+');
        });

        // Students-domain pages: directory + presence + registrations.
        Route::controller(\App\Http\Controllers\Backoffice\Crm\CrmStudentsController::class)->group(function () {
            Route::get('/students',               'students')->name('students');
            Route::get('/session-presence',       'sessionPresence')->name('session-presence');
            Route::get('/registrations',          'registrations')->name('registrations');
        });

        // Payment-domain pages: list / checks / allocations / collection.
        Route::controller(\App\Http\Controllers\Backoffice\Crm\CrmPaymentsController::class)->group(function () {
            Route::get('/payments',               'payments')->name('payments');
            Route::get('/payment-checks',         'paymentChecks')->name('payment-checks');
            Route::get('/payment-allocations',    'paymentAllocations')->name('payment-allocations');
            Route::get('/payment-collection',     'paymentCollection')->name('payment-collection');
        });

        // Groups + classes + matrix export + subscription services + salaries.
        Route::controller(\App\Http\Controllers\Backoffice\Crm\CrmGroupsController::class)->group(function () {
            Route::get('/groups/classes',         'groupsClasses')->name('groups.classes');
            Route::get('/groups/level-sessions',  'groupsLevelSessions')->name('groups.level-sessions');
            Route::post('/groups/classes/{classId}/payment-matrix',        'classPaymentMatrix')->name('groups.classes.payment-matrix')
                ->where('classId', '[0-9]+');
            Route::post('/groups/classes/{classId}/payment-matrix/export', 'classPaymentMatrixExport')->name('groups.classes.payment-matrix.export')
                ->where('classId', '[0-9]+');
            Route::get('/subscription-services',  'subscriptionServices')->name('subscription-services');
            Route::get('/employee-salaries',      'employeeSalaries')->name('employee-salaries');
        });

        // Analytics dashboards.
        Route::controller(\App\Http\Controllers\Backoffice\Crm\CrmInsightsController::class)->group(function () {
            Route::get('/insights/cash-handlers',                  'cashHandlers')->name('insights.cash-handlers');
            Route::get('/insights/reconciliation',                 'reconciliation')->name('insights.reconciliation');
            Route::get('/insights/retention',                      'retention')->name('insights.retention');
            Route::get('/insights/forecast',                       'forecast')->name('insights.forecast');
            Route::get('/insights/payment-activity',               'paymentActivity')->name('insights.payment-activity');
            Route::get('/insights/payment-history/{paymentId}',    'paymentHistory')->name('insights.payment-history')
                ->where('paymentId', '[0-9]+');
            Route::get('/insights/advances',                       'advances')->name('insights.advances');
            Route::get('/group-evolution',                         'groupEvolution')->name('group-evolution');
        });

        // Center Performance Dashboard
        Route::controller(\App\Http\Controllers\Backoffice\Crm\CrmCenterPerformanceController::class)->group(function () {
            Route::get('/center-performance', 'index')->name('center-performance');
        });
    });
