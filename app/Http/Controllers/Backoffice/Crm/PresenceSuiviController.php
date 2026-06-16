<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use App\Services\Crm\Stats\PresenceSuiviService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PresenceSuiviController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
        protected PresenceSuiviService $service,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    public function index(Request $request): View
    {
        $storeId    = $this->currentStrStoreId();
        $yearMonth  = $request->query('month', Carbon::today('Africa/Casablanca')->format('Y-m'));
        $bustCache  = $request->boolean('refresh');

        if ($bustCache) {
            // Flush all presence-related caches for this store + month
            Cache::forget("crm.presence_suivi.{$storeId}.{$yearMonth}");
            Cache::forget("crm.presence_suivi.totals.{$storeId}");
            Cache::forget("crm.presence_suivi.employees.{$storeId}");
            Cache::forget("crm.presence_suivi.global.{$yearMonth}");
            Cache::forget("crm.presence_suivi.details.{$storeId}.saisie");
            Cache::forget("crm.presence_suivi.details.{$storeId}.draft");

            // Rebuild the monthly aggregate so totals reflect the latest raw_data in DB.
            Artisan::call('crm:build-presence-summary', ['--all' => true, '--months' => 3]);
        }

        $data         = $this->service->buildMonth($storeId, $yearMonth);
        $globalFraud  = $this->service->globalFraud($yearMonth);
        $totals       = $this->service->allTimeTotals($storeId);
        $employeeStats = $this->service->employeeStats($storeId);

        $prevMonth = Carbon::parse($yearMonth . '-01')->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($yearMonth . '-01')->addMonth()->format('Y-m');

        return $this->view('backoffice.crm.presence-suivi.index', array_merge($data, [
            'storeId'     => $storeId,
            'yearMonth'   => $yearMonth,
            'prevMonth'   => $prevMonth,
            'nextMonth'   => $nextMonth,
            'monthLabel'  => Carbon::parse($yearMonth . '-01')->translatedFormat('F Y'),
            'globalFraud'  => $globalFraud,
            'totals'       => $totals,
            'teacherStats' => $employeeStats,
        ]));
    }

    public function details(Request $request): JsonResponse
    {
        $storeId = $this->currentStrStoreId();
        $status  = in_array($request->query('status'), ['saisie', 'draft']) ? $request->query('status') : 'saisie';

        $groups = $this->service->groupDetails($storeId, $status);

        return response()->json(['groups' => $groups]);
    }

    /**
     * Statistiques de présence — liste des séances avec présents/absents.
     */
    public function stats(Request $request): View
    {
        $storeId   = $this->currentStrStoreId();
        $startDate = $request->query('startDate') ?: Carbon::today('Africa/Casablanca')->startOfMonth()->toDateString();
        $endDate   = $request->query('endDate')   ?: Carbon::today('Africa/Casablanca')->toDateString();
        $classId   = $request->filled('classId') ? (int) $request->query('classId') : null;

        $data    = $this->service->sessionStats($storeId, $startDate, $endDate, $classId);
        $classes = $this->service->classOptions($storeId);

        return $this->view('backoffice.crm.presence-suivi.stats', array_merge($data, [
            'storeId'   => $storeId,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'classId'   => $classId,
            'classes'   => $classes,
        ]));
    }

    /**
     * Drill into a single séance: per-student présents / absents.
     */
    public function sessionDetail(Request $request): JsonResponse
    {
        $classId = (int) $request->query('classId');
        $date    = $request->query('date');

        if (!$classId || !$date) {
            return response()->json(['error' => 'classId et date sont requis.'], 422);
        }

        try {
            Carbon::parse($date);
        } catch (\Throwable) {
            return response()->json(['error' => 'Date invalide.'], 422);
        }

        return response()->json($this->service->sessionDetail($classId, $date));
    }

    /**
     * POST /presence-suivi/resync
     *
     * Pulls the last 2 months of attendance from Wimschool (updating PRESENCE_STATUS
     * for sessions where reception entered absence via email), then rebuilds the
     * presence_summary aggregate. Returns JSON so the UI can poll or redirect.
     */
    public function resync(Request $request): JsonResponse
    {
        $storeId   = $this->currentStrStoreId();
        $yearMonth = $request->input('month', Carbon::today('Africa/Casablanca')->format('Y-m'));

        // Prevent concurrent re-syncs
        if (Cache::has('crm.presence_suivi.resync.lock')) {
            return response()->json(['status' => 'locked', 'message' => 'Synchronisation déjà en cours, veuillez patienter.'], 409);
        }
        Cache::put('crm.presence_suivi.resync.lock', true, 300);

        try {
            // 1. Re-pull attendance rows from Wimschool (updates PRESENCE_STATUS in raw_data)
            Artisan::call('crm:sync-attendance', [
                '--months'    => 2,
                '--max-pages' => 60,
                '--delay'     => 200,
            ]);

            // 2. Rebuild the aggregate table from the freshly-synced rows
            Artisan::call('crm:build-presence-summary', ['--all' => true, '--months' => 3]);

            // 3. Flush all presence caches so next page load reads the rebuilt data
            foreach (['saisie', 'draft'] as $s) {
                Cache::forget("crm.presence_suivi.details.{$storeId}.{$s}");
            }
            Cache::forget("crm.presence_suivi.totals.{$storeId}");
            Cache::forget("crm.presence_suivi.employees.{$storeId}");
            Cache::forget("crm.presence_suivi.global.{$yearMonth}");
            Cache::forget("crm.presence_suivi.{$storeId}.{$yearMonth}");

        } finally {
            Cache::forget('crm.presence_suivi.resync.lock');
        }

        return response()->json(['status' => 'ok', 'message' => 'Synchronisation terminée. Les statuts de présence sont à jour.']);
    }
}
