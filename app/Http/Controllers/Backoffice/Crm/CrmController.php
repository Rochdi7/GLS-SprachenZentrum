<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\CrmException;
use App\Services\Crm\Stats\CrmStatsService;
use App\Services\Crm\Stats\DuplicateFinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

/**
 * CRM overview controller.
 *
 * Lightweight pages that don't fit any specific domain: the landing index,
 * stats dashboard, duplicate detector, LOV browser, center-switcher form,
 * and the student type-ahead JSON endpoint. Per-domain CRUD-style pages
 * live in their own controllers (CrmStudentsController, CrmPaymentsController,
 * CrmGroupsController, CrmInsightsController).
 */
class CrmController extends BaseCrmController
{
    /** Persist the selected center in session, then redirect back. */
    public function setCenter(Request $r): RedirectResponse
    {
        $storeId = $r->input('crm_store_id');
        $this->centers->setStoreId($storeId ? (int) $storeId : null);

        return back();
    }

    public function index(): View
    {
        return $this->view('backoffice.crm.index');
    }

    /**
     * Cache key that crm:sync-all sets while a full sync is in flight.
     * Shared with CrmSyncAllCommand so the manual "Actualiser" button and the
     * every-2h scheduler never run two full syncs at once.
     */
    private const SYNC_LOCK_KEY = 'crm.sync-all.lock';

    /**
     * POST /backoffice/crm/resync
     *
     * Manually trigger a full CRM sync (the same crm:sync-all the scheduler
     * runs every 2h). Launched detached in the background so the HTTP request
     * returns immediately — crm:sync-all takes several minutes and would blow
     * PHP's max_execution_time if run inline.
     *
     * Duplicate-click safe: if a sync is already running (button OR scheduler),
     * returns 409 without starting a second one. The lock is set here up-front
     * and the command respects it via --force being absent.
     */
    public function resync(): JsonResponse
    {
        if (Cache::has(self::SYNC_LOCK_KEY)) {
            return response()->json([
                'status'  => 'locked',
                'message' => 'Une synchronisation est déjà en cours. Veuillez patienter.',
            ], 409);
        }

        // Set the lock up-front so a rapid second click is rejected even before
        // the background process has booted and set its own lock. TTL matches
        // the command's LOCK_TTL (2h) so a crashed run self-clears.
        Cache::put(self::SYNC_LOCK_KEY, now()->toIso8601String(), 7200);

        try {
            // Launch detached: nohup + '&' so PHP-FPM does not wait for it.
            // --force lets the command proceed past its own lock check (we own
            // the lock we just set); the command clears the lock when it ends.
            $php     = PHP_BINARY;
            $artisan = base_path('artisan');
            $log     = storage_path('logs/crm-sync-all.log');

            $cmd = sprintf(
                'nohup %s %s crm:sync-all --force >> %s 2>&1 &',
                escapeshellarg($php),
                escapeshellarg($artisan),
                escapeshellarg($log)
            );

            $process = Process::fromShellCommandline($cmd, base_path());
            $process->setTimeout(5);
            $process->disableOutput();
            $process->start();
            // Do NOT wait() — the '&' detaches it; we just need FPM to have
            // handed it to the shell.

            Log::info('crm:sync-all launched manually via Actualiser button', [
                'user_id' => optional(request()->user())->id,
            ]);

            return response()->json([
                'status'  => 'started',
                'message' => 'Synchronisation lancée. Les données seront à jour dans quelques minutes.',
            ]);
        } catch (\Throwable $e) {
            // Launch failed — release the lock so the button isn't stuck.
            Cache::forget(self::SYNC_LOCK_KEY);
            Log::error('Failed to launch manual crm:sync-all', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Impossible de lancer la synchronisation. Réessayez plus tard.',
            ], 500);
        }
    }

    /**
     * GET /backoffice/crm/resync/status
     *
     * Lightweight poll target for the Actualiser button so it can show a
     * spinner while a sync (manual or scheduled) is running and re-enable
     * itself + refresh the page once it finishes.
     */
    public function resyncStatus(): JsonResponse
    {
        return response()->json([
            'running'   => Cache::has(self::SYNC_LOCK_KEY),
            'last_sync' => optional($this->lastSyncAt())->toIso8601String(),
            // Freshly-rendered badge HTML so the client can swap it in place
            // without a full page reload (keeps the Blade color/label logic
            // as the single source of truth).
            'badge_html' => view('backoffice.crm.partials._sync_badge', [
                'crmLastSync' => $this->lastSyncAt(),
            ])->render(),
        ]);
    }

    /**
     * JSON endpoint that backs the student type-ahead in CRM filters.
     *
     * Accepts ?q=ahmed&strStoreId=42 and returns up to 25 students whose
     * first or last name contains the term. The /students endpoint accepts
     * firstName and lastName as separate filters, so we hit it twice and
     * merge.
     *
     * Response shape (matches the LOV provider's normalize() contract):
     *   [['id' => 123, 'name' => 'Ahmed Nadiri', 'reference' => '237SL126'], ...]
     */
    public function studentsSearch(Request $r): JsonResponse
    {
        $q = trim((string) $r->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $strStoreId = $this->currentStrStoreId();
        $crm        = $this->scopedCrm();

        $rows = [];
        $seen = [];

        $calls = [
            ['firstName' => $q, 'lastName'  => null],
            ['firstName' => null, 'lastName' => $q],
        ];

        foreach ($calls as $args) {
            try {
                $resp = $crm->students()->list(
                    page: 0,
                    size: 25,
                    includeTotal: false,
                    strStoreId: $strStoreId,
                    firstName: $args['firstName'],
                    lastName:  $args['lastName'],
                );
                foreach (($resp['data'] ?? []) as $row) {
                    $id = $row['ID'] ?? $row['STUDENT_ID'] ?? null;
                    if ($id === null || isset($seen[$id])) continue;
                    $seen[$id] = true;

                    $name = trim(($row['FIRST_NAME'] ?? '') . ' ' . ($row['LAST_NAME'] ?? '')) ?: ('#' . $id);
                    $rows[] = [
                        'id'        => (int) $id,
                        'name'      => $name,
                        'reference' => $row['REFERENCE'] ?? null,
                    ];
                }
            } catch (\Throwable) {
                // One failing call is non-fatal — we may still have results from the other.
            }
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        $rows = array_slice($rows, 0, 25);

        return response()->json(['data' => $rows]);
    }

    /**
     * Per-center evolution dashboard: KPI snapshot + monthly payments trend.
     */
    public function stats(Request $r, CrmStatsService $stats): View
    {
        $preset = $r->query('range', 'year');
        [$start, $end] = match ($preset) {
            'today' => [now()->toDateString(), now()->toDateString()],
            '7d'    => [now()->subDays(7)->toDateString(),  now()->toDateString()],
            '30d'   => [now()->subDays(30)->toDateString(), now()->toDateString()],
            'year'  => [now()->startOfYear()->toDateString(), now()->toDateString()],
            'custom' => [$r->query('startDate'), $r->query('endDate')],
            default  => [null, null],
        };

        $currentYear = (int) now()->year;
        $chartYear   = (int) $r->query('year', $currentYear);
        $chartYear   = max($currentYear - 5, min($chartYear, $currentYear + 1));
        $chartStore  = $this->currentStrStoreId();

        $kpis   = $stats->perCenterKpis($start, $end);
        $trend  = $stats->paymentsTrend(months: 6);
        $annual = $stats->annualSummary($chartYear, $chartStore);

        return $this->view('backoffice.crm.stats', [
            'kpis'        => $kpis,
            'trend'       => $trend,
            'preset'      => $preset,
            'start'       => $start,
            'end'         => $end,
            'annual'      => $annual,
            'chartYear'   => $chartYear,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Duplicate-detection scan over students returned by the API.
     * Buckets by phone / WhatsApp / email / CIN / (name + store).
     */
    public function duplicates(Request $r, DuplicateFinder $finder): View
    {
        $storeId   = $this->currentStrStoreId();
        $bustCache = $r->boolean('refresh');

        $report = $finder->find($storeId, bustCache: $bustCache);

        return $this->view('backoffice.crm.duplicates', [
            'report'  => $report,
            'storeId' => $storeId,
        ]);
    }

    public function lov(Request $r, string $kind): View
    {
        $method = match ($kind) {
            'school-levels'                          => 'schoolLevels',
            'registration-statuses'                  => 'registrationStatuses',
            'registration-conventions'               => 'registrationConventions',
            'registration-change-status-reasons'     => 'registrationChangeStatusReasons',
            'payment-types'                          => 'paymentTypes',
            'payment-statuses'                       => 'paymentStatuses',
            'payment-methods'                        => 'paymentMethods',
            'payment-check-statuses'                 => 'paymentCheckStatuses',
            'level-session-packages'                 => 'levelSessionPackages',
            'categories'                             => 'categories',
            'banks'                                  => 'banks',
            default                                  => abort(404),
        };

        $limit = (int) $r->query('limit', 100);

        $payload = null;
        $error   = null;
        try {
            $payload = $this->scopedCrm()->lov()->{$method}($limit);
        } catch (CrmException $e) {
            $error = $e;
        }

        return $this->view('backoffice.crm.lov', [
            'kind'    => $kind,
            'limit'   => $limit,
            'payload' => $payload,
            'error'   => $error,
        ]);
    }
}
