<?php

namespace App\Services\Crm\Stats;

use App\Models\Site;
use App\Services\Crm\HomeschoolClient;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Statistics aggregator for the Homeschool CRM.
 *
 * Strategy:
 *   - The API's pagination.totalElements field gives us per-store counts cheaply
 *     (size=1, includeTotal=true). Fetching just the count is ~kilobytes.
 *   - For 7 centers × N metrics we fan out via Http::pool() so the total wall
 *     time is one round-trip, not N×7.
 *   - Results cached for 5 minutes to avoid hammering the API on refresh.
 *
 * READ-ONLY. No local DB writes, no sync state. The API is the source of truth.
 */
class CrmStatsService
{
    public const CACHE_TTL = 300; // 5 minutes

    public function __construct(protected HomeschoolClient $client)
    {
    }

    /**
     * Per-center KPI snapshot for the given date window.
     *
     * Returns an array keyed by site id:
     *   [
     *     site_id => [
     *       'site'          => Site,
     *       'students'      => int,
     *       'registrations' => int,
     *       'payments'      => int,
     *       'classes'       => int,
     *     ],
     *     ...
     *   ]
     *
     * @return array<int, array<string,mixed>>
     */
    public function perCenterKpis(?string $startDate = null, ?string $endDate = null): array
    {
        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get();
        if ($sites->isEmpty()) {
            return [];
        }

        $cacheKey = 'crm.stats.kpis:' . md5(($startDate ?? '') . '|' . ($endDate ?? '') . '|' . $sites->pluck('crm_store_id')->implode(','));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sites, $startDate, $endDate) {
            // Build the request list. Each item is [siteId, metric, path, query].
            $jobs = [];
            foreach ($sites as $site) {
                $base = ['size' => 1, 'includeTotal' => 'true', 'strStoreId' => $site->crm_store_id];
                $dated = $base;
                if ($startDate) $dated['startDate'] = $startDate;
                if ($endDate)   $dated['endDate']   = $endDate;

                $jobs[] = [$site->id, 'students',      '/api/external/v1/students',      ['page' => 0] + $base];
                $jobs[] = [$site->id, 'registrations', '/api/external/v1/registrations', ['page' => 0] + $dated];
                $jobs[] = [$site->id, 'payments',      '/api/external/v1/payments',      ['page' => 0] + $dated];
                $jobs[] = [$site->id, 'classes',       '/api/external/v1/groups/classes',['page' => 0] + $base];
            }

            $responses = $this->fanOut($jobs);

            // Initialize result skeleton.
            $out = [];
            foreach ($sites as $site) {
                $out[$site->id] = [
                    'site'          => $site,
                    'students'      => null,
                    'registrations' => null,
                    'payments'      => null,
                    'classes'       => null,
                    'errors'        => [],
                ];
            }

            foreach ($jobs as $idx => $job) {
                [$siteId, $metric] = $job;
                $response = $responses[$idx] ?? null;
                if (!$response instanceof Response) {
                    $out[$siteId]['errors'][$metric] = 'no response';
                    continue;
                }
                if (!$response->successful()) {
                    $out[$siteId]['errors'][$metric] = 'HTTP ' . $response->status();
                    continue;
                }
                $body = $response->json();
                $out[$siteId][$metric] = (int) ($body['pagination']['totalElements'] ?? 0);
            }

            return $out;
        });
    }

    /**
     * Monthly payment-volume time series, last 6 months, per center.
     *
     * Returns:
     *   [
     *     'labels'  => ['2025-12', '2026-01', ...],
     *     'series'  => [
     *        site_id => ['site' => Site, 'counts' => [int, int, ...]],
     *        ...
     *     ],
     *   ]
     *
     * @return array<string,mixed>
     */
    public function paymentsTrend(int $months = 6): array
    {
        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get();
        if ($sites->isEmpty()) {
            return ['labels' => [], 'series' => []];
        }

        $cacheKey = 'crm.stats.trend:payments:' . $months . ':' . $sites->pluck('crm_store_id')->implode(',');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sites, $months) {
            // Build month buckets (oldest → newest).
            $buckets = [];
            $cursor = Carbon::now()->startOfMonth()->subMonths($months - 1);
            for ($i = 0; $i < $months; $i++) {
                $buckets[] = [
                    'label' => $cursor->format('Y-m'),
                    'start' => $cursor->copy()->startOfMonth()->toDateString(),
                    'end'   => $cursor->copy()->endOfMonth()->toDateString(),
                ];
                $cursor->addMonth();
            }

            // Fan out: site × month → /payments?size=1&startDate=...&endDate=...
            $jobs = [];
            foreach ($sites as $site) {
                foreach ($buckets as $bIdx => $b) {
                    $jobs[] = [
                        $site->id,
                        $bIdx,
                        '/api/external/v1/payments',
                        [
                            'page'         => 0,
                            'size'         => 1,
                            'includeTotal' => 'true',
                            'strStoreId'   => $site->crm_store_id,
                            'startDate'    => $b['start'],
                            'endDate'      => $b['end'],
                        ],
                    ];
                }
            }

            $responses = $this->fanOut($jobs);

            // Build the series.
            $series = [];
            foreach ($sites as $site) {
                $series[$site->id] = [
                    'site'   => $site,
                    'counts' => array_fill(0, count($buckets), 0),
                ];
            }
            foreach ($jobs as $idx => $job) {
                [$siteId, $bIdx] = $job;
                $response = $responses[$idx] ?? null;
                if ($response instanceof Response && $response->successful()) {
                    $series[$siteId]['counts'][$bIdx] = (int) ($response->json('pagination.totalElements') ?? 0);
                }
            }

            return [
                'labels' => array_column($buckets, 'label'),
                'series' => $series,
            ];
        });
    }

    /**
     * Fan out an array of [siteId, metric/bIdx, path, query] jobs via Http::pool().
     *
     * Returns a map of "job_{idx}" => Response. Numeric keys are unreliable
     * inside a pool closure, so we always use named keys via $pool->as().
     *
     * @param  array<int, array{0:int,1:int|string,2:string,3:array}>  $jobs
     * @return array<int, Response|\Throwable|null>
     */
    protected function fanOut(array $jobs): array
    {
        $token   = config('crm.token');
        $baseUrl = rtrim(config('crm.base_url'), '/');
        $verify  = (bool) config('crm.verify_ssl', true);
        $timeout = (int) config('crm.timeout', 15);

        // Chunk to avoid hammering the API with 28+ concurrent connections.
        // 10 in flight at a time is a sane default for an external SaaS.
        $chunks = array_chunk($jobs, 10, preserve_keys: true);
        $out = [];

        foreach ($chunks as $chunk) {
            $named = Http::pool(function (Pool $pool) use ($chunk, $token, $baseUrl, $verify, $timeout) {
                $requests = [];
                foreach ($chunk as $idx => $job) {
                    [, , $path, $query] = $job;
                    $r = $pool->as("job_{$idx}")
                        ->withToken($token)
                        ->acceptJson()
                        ->timeout($timeout);
                    if (!$verify) {
                        $r = $r->withoutVerifying();
                    }
                    $requests[] = $r->get($baseUrl . $path, $query);
                }
                return $requests;
            });

            // Re-map "job_42" => 42 to align with the original numeric job index.
            foreach (array_keys($chunk) as $idx) {
                $out[$idx] = $named["job_{$idx}"] ?? null;
            }
        }

        return $out;
    }

    /**
     * Invalidate the stats cache. Call from an artisan command or admin button.
     */
    public function flush(): void
    {
        // Brute-force: scan keys we know we use. There are only a few cache entries.
        foreach (Cache::getStore()->getPrefix() ? [] : [] as $k) { /* noop */ }
        // The cache driver may not support wildcards; rely on TTL otherwise.
        Cache::forget('crm.stats.trend:payments:6:' . Site::whereNotNull('crm_store_id')->orderBy('name')->pluck('crm_store_id')->implode(','));
    }
}
