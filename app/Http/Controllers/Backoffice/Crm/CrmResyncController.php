<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Models\CrmResyncLog;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * On-demand CRM re-sync.
 *
 * Exposes a single POST endpoint that re-pulls one or more data domains from
 * the Wimschool API, rebuilds any dependent aggregates, and flushes the
 * matching cache entries — so a user who just edited data in the CRM web UI
 * can reflect those changes immediately without waiting for the scheduler.
 *
 * Each domain maps to the same artisan commands used by crm:sync-all, with
 * lighter options (fewer pages, shorter delay) suited for interactive use.
 */
class CrmResyncController extends BaseCrmController
{
    private const LOCK_KEY = 'crm.resync.lock';
    private const LOCK_TTL = 600; // 10 min max

    /**
     * Domain definitions.
     *
     * Each entry:
     *   'label'    — human-readable name shown in the UI
     *   'steps'    — artisan [command => options] pairs, in order
     *   'cache'    — callable(?int $storeId): array<string> of cache keys to forget
     */
    private function domains(): array
    {
        return [
            'attendance' => [
                'label' => 'Présences / Absences',
                'steps' => [
                    'crm:sync-attendance'       => ['--months' => 2, '--max-pages' => 60, '--delay' => 200],
                    'crm:build-presence-summary' => ['--all' => true, '--months' => 3],
                ],
                'cache' => function (?int $sid): array {
                    $yearMonth = Carbon::today('Africa/Casablanca')->format('Y-m');
                    return [
                        "crm.presence_suivi.{$sid}.{$yearMonth}",
                        "crm.presence_suivi.totals.{$sid}",
                        "crm.presence_suivi.employees.{$sid}",
                        "crm.presence_suivi.global.{$yearMonth}",
                        "crm.presence_suivi.details.{$sid}.saisie",
                        "crm.presence_suivi.details.{$sid}.draft",
                    ];
                },
            ],

            'collections' => [
                'label' => 'Recouvrement (créances)',
                'steps' => [
                    'crm:sync-collections' => ['--all' => true, '--delay' => 300],
                ],
                'cache' => function (?int $sid): array {
                    $suffix = $sid ?: 'all';
                    return [
                        "crm.collections.kpis:{$suffix}",
                        "crm.collections.top_debtors:{$suffix}:20",
                        "crm.collections.upcoming:{$suffix}:14",
                        "crm.collections.aging:{$suffix}",
                        'crm.collections.perf_by_center',
                    ];
                },
            ],

            'payments' => [
                'label' => 'Paiements (snapshot)',
                'steps' => [
                    'crm:snapshot-payments' => ['--months' => 2],
                ],
                'cache' => function (?int $sid): array {
                    $storeIds = Site::whereNotNull('crm_store_id')->pluck('crm_store_id')->implode(',');
                    $year = Carbon::today()->year;
                    return [
                        'crm.stats.trend:payments:6:' . $storeIds,
                        'crm.stats.annual:' . $year . ':' . ($sid ?? 'all'),
                        'crm.stats.annual:' . ($year - 1) . ':' . ($sid ?? 'all'),
                    ];
                },
            ],

            'registrations' => [
                'label' => 'Inscriptions',
                'steps' => [
                    'crm:sync-registrations' => ['--all' => true],
                ],
                'cache' => function (?int $sid): array {
                    return []; // No dedicated cache keys — re-read hits DB directly
                },
            ],

            'allocations' => [
                'label' => 'Allocations paiements',
                'steps' => [
                    'crm:sync-payment-allocations' => ['--all' => true, '--months' => 6, '--delay' => 500],
                ],
                'cache' => function (?int $sid): array {
                    return []; // GroupEvolution rebuilds its own cache on next load
                },
            ],

            'classes' => [
                'label' => 'Groupes / Étudiants',
                'steps' => [
                    'homeschool:mirror-core' => ['--months' => 2],
                ],
                'cache' => function (?int $sid): array {
                    return []; // LOVs re-fetch on next use; no long-lived class cache keys
                },
            ],

            'all' => [
                'label' => 'Tout synchroniser',
                'steps' => [
                    'homeschool:mirror-core'         => ['--months' => 2],
                    'crm:sync-attendance'             => ['--months' => 2, '--max-pages' => 60, '--delay' => 200],
                    'crm:sync-collections'            => ['--all' => true, '--delay' => 300],
                    'crm:snapshot-payments'           => ['--months' => 2],
                    'crm:sync-registrations'          => ['--all' => true],
                    'crm:sync-payment-allocations'    => ['--all' => true, '--months' => 6, '--delay' => 500],
                    'crm:build-presence-summary'      => ['--all' => true, '--months' => 3],
                ],
                'cache' => function (?int $sid): array {
                    $yearMonth = Carbon::today('Africa/Casablanca')->format('Y-m');
                    $storeIds  = Site::whereNotNull('crm_store_id')->pluck('crm_store_id')->implode(',');
                    $year      = Carbon::today()->year;
                    $suffix    = $sid ?: 'all';
                    return [
                        // Presence
                        "crm.presence_suivi.{$sid}.{$yearMonth}",
                        "crm.presence_suivi.totals.{$sid}",
                        "crm.presence_suivi.employees.{$sid}",
                        "crm.presence_suivi.global.{$yearMonth}",
                        "crm.presence_suivi.details.{$sid}.saisie",
                        "crm.presence_suivi.details.{$sid}.draft",
                        // Collections
                        "crm.collections.kpis:{$suffix}",
                        "crm.collections.top_debtors:{$suffix}:20",
                        "crm.collections.upcoming:{$suffix}:14",
                        "crm.collections.aging:{$suffix}",
                        'crm.collections.perf_by_center',
                        // Payments / stats
                        'crm.stats.trend:payments:6:' . $storeIds,
                        'crm.stats.annual:' . $year . ':' . ($sid ?? 'all'),
                        'crm.stats.annual:' . ($year - 1) . ':' . ($sid ?? 'all'),
                        // Stats dashboard
                        "crm.stats.dashboard:6:{$suffix}",
                        "crm.stats.dashboard:3:{$suffix}",
                    ];
                },
            ],
        ];
    }

    /**
     * GET /crm/resync
     * Shows the resync dashboard page.
     */
    public function index(): \Illuminate\View\View
    {
        $domains  = collect($this->domains())->map(fn ($d) => $d['label'])->toArray();
        $isLocked = Cache::has(self::LOCK_KEY);

        $history = CrmResyncLog::with('user')
            ->latest()
            ->limit(50)
            ->get();

        return $this->view('backoffice.crm.resync.index', [
            'domains'  => $domains,
            'isLocked' => $isLocked,
            'history'  => $history,
        ]);
    }

    /**
     * POST /crm/resync
     * Runs the requested domain sync, or unlocks a stuck lock.
     */
    public function run(Request $request): JsonResponse
    {
        // Special action: force-clear a stuck lock without syncing
        if ($request->input('action') === 'unlock') {
            Cache::forget(self::LOCK_KEY);
            return response()->json(['status' => 'ok', 'message' => 'Verrou libéré. Vous pouvez relancer la synchronisation.']);
        }

        $domain = $request->input('domain', 'all');

        if (!array_key_exists($domain, $this->domains())) {
            return response()->json(['status' => 'error', 'message' => 'Domaine invalide.'], 422);
        }

        if (Cache::has(self::LOCK_KEY)) {
            return response()->json([
                'status'  => 'locked',
                'message' => 'Une synchronisation est déjà en cours. Réessayez dans quelques minutes.',
            ], 409);
        }

        Cache::put(self::LOCK_KEY, ['domain' => $domain, 'started_at' => now()->toIso8601String()], self::LOCK_TTL);

        $storeId  = $this->currentStrStoreId();
        $results  = [];
        $wallStart = microtime(true);

        try {
            $def = $this->domains()[$domain];

            foreach ($def['steps'] as $command => $options) {
                $t0 = microtime(true);
                try {
                    Artisan::call($command, $options);
                    $results[] = [
                        'step'    => $command,
                        'status'  => 'ok',
                        'elapsed' => round(microtime(true) - $t0, 1) . 's',
                    ];
                } catch (\Throwable $e) {
                    $results[] = [
                        'step'    => $command,
                        'status'  => 'error',
                        'error'   => $e->getMessage(),
                        'elapsed' => round(microtime(true) - $t0, 1) . 's',
                    ];
                }
            }

            // Flush cache keys for this domain
            foreach (($def['cache'])($storeId) as $key) {
                Cache::forget($key);
            }

        } finally {
            Cache::forget(self::LOCK_KEY);
        }

        $hasErrors = collect($results)->contains('status', 'error');
        $finalStatus = $hasErrors ? 'partial' : 'ok';
        $duration = (int) round(microtime(true) - $wallStart);

        // Persist audit record — who triggered the sync, when, and what happened
        CrmResyncLog::create([
            'user_id'          => Auth::id(),
            'domain'           => $domain,
            'domain_label'     => $this->domains()[$domain]['label'],
            'status'           => $finalStatus,
            'crm_store_id'     => $storeId,
            'steps'            => $results,
            'duration_seconds' => $duration,
        ]);

        return response()->json([
            'status'  => $finalStatus,
            'domain'  => $domain,
            'label'   => $this->domains()[$domain]['label'],
            'results' => $results,
            'message' => $hasErrors
                ? 'Synchronisation terminée avec des erreurs. Vérifiez les détails ci-dessous.'
                : 'Synchronisation terminée avec succès. Les données sont à jour.',
        ]);
    }
}
