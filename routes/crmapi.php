<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM (Wimschool External API) — READ-ONLY proxy pages
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
            Route::get('/group-evolution/drill',                   'groupEvolutionDrill')->name('group-evolution.drill');
        });

        // Collections dashboard (Module 2).
        Route::prefix('collections')->name('collections.')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Backoffice\Crm\CollectionsController::class, 'index'])->name('index');
            Route::post('/refresh', [\App\Http\Controllers\Backoffice\Crm\CollectionsController::class, 'refresh'])->name('refresh');
            Route::get('/drill',   [\App\Http\Controllers\Backoffice\Crm\CollectionsController::class, 'drill'])->name('drill');
        });

        // Suivi présences — calendrier anti-fraude.
        Route::get('/presence-suivi',          [\App\Http\Controllers\Backoffice\Crm\PresenceSuiviController::class, 'index'])->name('presence-suivi');
        Route::get('/presence-suivi/details',  [\App\Http\Controllers\Backoffice\Crm\PresenceSuiviController::class, 'details'])->name('presence-suivi.details');
        // Legacy narrow resync kept for backward-compat; now delegates to CrmResyncController
        Route::post('/presence-suivi/resync',  [\App\Http\Controllers\Backoffice\Crm\PresenceSuiviController::class, 'resync'])->name('presence-suivi.resync');

        // Global on-demand re-sync dashboard (all CRM data domains).
        Route::get('/resync',  [\App\Http\Controllers\Backoffice\Crm\CrmResyncController::class, 'index'])->name('resync');
        Route::post('/resync', [\App\Http\Controllers\Backoffice\Crm\CrmResyncController::class, 'run'])->name('resync.run');

        // Statistiques par centre (encaissement + recouvrement + inscriptions).
        Route::get('/statistiques',                      [\App\Http\Controllers\Backoffice\Crm\StatsController::class, 'index'])->name('statistiques');
        Route::post('/statistiques/refresh',             [\App\Http\Controllers\Backoffice\Crm\StatsController::class, 'refresh'])->name('statistiques.refresh');
        Route::get('/statistiques/encaissement-range',   [\App\Http\Controllers\Backoffice\Crm\StatsController::class, 'encaissementRange'])->name('statistiques.encaissement-range');
        Route::get('/statistiques/comparaison',          [\App\Http\Controllers\Backoffice\Crm\StatsController::class, 'comparaison'])->name('statistiques.comparaison');
        Route::get('/statistiques/comparaison/data',     [\App\Http\Controllers\Backoffice\Crm\StatsController::class, 'comparaisonData'])->name('statistiques.comparaison.data');

        // Agent / Call-Center dashboard.
        Route::prefix('agent')->name('agent.')->controller(\App\Http\Controllers\Backoffice\Crm\AgentDashboardController::class)->group(function () {
            Route::get('/',                    'index')->name('index');
            Route::get('/call-today',          'callToday')->name('call-today');
            Route::get('/follow-ups',          'followUps')->name('follow-ups');
            Route::post('/follow-ups',         'saveFollowUp')->name('follow-ups.save');
            Route::get('/unpaid',              'unpaid')->name('unpaid');
            Route::get('/new-without-payment', 'newWithoutPayment')->name('new-without-payment');
            Route::get('/student-card',        'studentCard')->name('student-card');
        });

        // Daily CEO Reports (Module 1).
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/',          [\App\Http\Controllers\Backoffice\Crm\DailyReportController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\Backoffice\Crm\DailyReportController::class, 'generate'])->name('generate');
            Route::get('/{date}',    [\App\Http\Controllers\Backoffice\Crm\DailyReportController::class, 'show'])->name('show')
                ->where('date', '\d{4}-\d{2}-\d{2}');
            Route::post('/{date}/resend', [\App\Http\Controllers\Backoffice\Crm\DailyReportController::class, 'resend'])->name('resend')
                ->where('date', '\d{4}-\d{2}-\d{2}');
        });
    });
