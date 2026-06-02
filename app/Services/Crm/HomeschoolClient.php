<?php

namespace App\Services\Crm;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Low-level HTTP client for the Homeschool External API v1.
 *
 * Holds the Bearer token, base URL, retry and timeout policy. All resource
 * classes go through this - never call Http::get() directly elsewhere in the
 * CRM module.
 *
 * Intentionally separate from the Laravel CRUD layer: this never touches
 * Eloquent or the local DB.
 */
class HomeschoolClient
{
    protected string $baseUrl;
    protected ?string $token;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retryTimes;
    protected int $retrySleepMs;
    protected bool $logEnabled;
    protected string $logChannel;
    protected bool $verifySsl;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Dispatch a background job to preload and cache multiple pages of a resource.
     */
    public function preload(string $path, array $query, int $pages = 3, int $startPage = 0): void
    {
        \App\Jobs\Crm\PreloadCrmResourceJob::dispatch(
            $path,
            $query,
            $this->token,
            $startPage,
            $pages
        );
    }

    public function __construct(?array $config = null)
    {
        $config ??= config('crm');

        $this->baseUrl        = rtrim($config['base_url'] ?? '', '/');
        $this->token          = $config['token'] ?? null;
        $this->timeout        = (int) ($config['timeout'] ?? 15);
        $this->connectTimeout = (int) ($config['connect_timeout'] ?? 5);
        $this->retryTimes     = (int) ($config['retry']['times'] ?? 2);
        $this->retrySleepMs   = (int) ($config['retry']['sleep_ms'] ?? 250);
        $this->logEnabled     = (bool) ($config['logging']['enabled'] ?? true);
        $this->logChannel     = (string) ($config['logging']['channel'] ?? 'stack');
        $this->verifySsl      = (bool) ($config['verify_ssl'] ?? true);
    }

    /**
     * GET request returning the decoded JSON body.
     *
     * Transparently cached in the Laravel cache for CRM_API_CACHE_TTL seconds
     * (default 60s). Pass `fresh: true` to bypass the cache for one call.
     *
     * @param  array<string,mixed>  $query
     * @return array<string,mixed>
     */
    public function get(string $path, array $query = [], bool $fresh = false): array
    {
        $ttl = (int) config('crm.cache_ttl', 60);
        if ($ttl <= 0) {
            return $this->send('GET', $path, query: $query);
        }

        // Cache key includes the token-suffix so per-center tokens don't collide.
        $key = 'crm.http:' . substr((string) $this->token, -8) . ':' . sha1($path . '|' . http_build_query($query));

        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, $ttl, function () use ($path, $query) {
            return $this->send('GET', $path, query: $query);
        });
    }

    /**
     * Paginate through a list endpoint in parallel batches via Http::pool().
     *
     * Strategy:
     *   1. Do one synchronous request (page=0) to learn `totalPages`.
     *   2. Fire all remaining pages in parallel batches of $concurrency.
     *   3. Return the merged `data` array.
     *
     * Falls back to sequential pagination if the API doesn't return totalPages
     * (e.g. when includeTotal=false). For lighter payloads, pass includeTotal=false
     * in the base query - Homeschool's COUNT query is the slowest part.
     *
     * @param  array<string,mixed>  $baseQuery  Filters (excluding page/size)
     * @return array<int, array<string,mixed>>  Flat list of all rows
     */
    public function pagedScan(
        string $path,
        array $baseQuery,
        int $pageSize = 25,
        int $maxPages = 80,
        int $concurrency = 2,
        int $interBatchDelayMs = 300,
    ): array {
        $startTime = microtime(true);
        $firstPageStart = microtime(true);
        $firstPage = $this->send('GET', $path, query: [
            'page' => 0,
            'size' => $pageSize,
            'includeTotal' => 'true',
        ] + $baseQuery);
        $firstPageTime = round(microtime(true) - $firstPageStart, 3);

        $rows = is_array($firstPage['data'] ?? null) ? array_values($firstPage['data']) : [];
        $totalPages = $firstPage['pagination']['totalPages']
            ?? $firstPage['totalPages']
            ?? null;

        if (!is_numeric($totalPages)) {
            return $this->pagedScanSequentialFallback(
                path: $path,
                baseQuery: $baseQuery,
                pageSize: $pageSize,
                maxPages: $maxPages,
                rows: $rows,
            );
        }

        $lastPage = min(max(1, (int) $totalPages), $maxPages);
        if ($lastPage <= 1) {
            return $rows;
        }

        $token = $this->token;
        $url   = $this->baseUrl . '/' . ltrim($path, '/');

        $pendingPages = range(1, $lastPage - 1);

        for ($attempt = 1; $attempt <= 3 && !empty($pendingPages); $attempt++) {
            if ($attempt === 2) {
                sleep(1);
            } elseif ($attempt === 3) {
                sleep(3);
            }

            $attemptPages = $pendingPages;
            $pendingPages = [];
            $batchIndex = 0;

            for ($start = 0; $start < count($attemptPages); $start += $concurrency) {
                if ($batchIndex > 0 && $interBatchDelayMs > 0) {
                    usleep($interBatchDelayMs * 1000);
                }
                $batchIndex++;

                $pageBatch = array_slice($attemptPages, $start, $concurrency);

                $responses = Http::pool(function (Pool $pool) use (
                    $pageBatch,
                    $baseQuery,
                    $pageSize,
                    $token,
                    $url
                ) {
                    $requests = [];
                    foreach ($pageBatch as $page) {
                        $request = $this->poolRequest($pool, "page_{$page}", $token);
                        $requests[] = $request->get($url, [
                            'page' => $page,
                            'size' => $pageSize,
                            'includeTotal' => 'false',
                        ] + $baseQuery);
                    }
                    return $requests;
                });

                foreach ($pageBatch as $page) {
                    $resp = $responses["page_{$page}"] ?? null;
                    if (!$resp instanceof Response) {
                        throw new CrmException(
                            "CRM API GET {$url} failed during paged scan.",
                            0,
                            ['errorCode' => 'POOL_ERROR', 'message' => "Missing pooled response for page {$page}."],
                        );
                    }

                    if ($resp->status() === 429) {
                        $pendingPages[] = $page;
                        continue;
                    }

                    if (!$resp->successful()) {
                        $this->handle($resp, 'GET', $url);
                    }

                    foreach (($resp->json('data') ?? []) as $row) {
                        $rows[] = $row;
                    }
                }
            }
        }

        if (!empty($pendingPages)) {
            $this->throwRateLimited($path, 'pagedScan', count($pendingPages));
        }

        $totalTime = round(microtime(true) - $startTime, 3);
        \Illuminate\Support\Facades\Log::info('HomeschoolClient::pagedScan complete', [
            'path' => $path,
            'baseQuery' => $baseQuery,
            'firstPageTime' => $firstPageTime,
            'totalPages' => $totalPages ?? 'unknown',
            'rowsFetched' => count($rows),
            'totalTime' => $totalTime
        ]);

        return $rows;
    }

    /**
     * Fire one parallel GET per query-variant and merge their `data` arrays.
     *
     * Use this when the API doesn't support a range filter (e.g. session-presence
     * only accepts a single `date`) but you need data across many values of that
     * filter. Each variant becomes its own HTTP call, fanned out 10-at-a-time.
     *
     * @param  string  $path            API path (e.g. /api/external/v1/session-presence)
     * @param  array<string,mixed>  $baseQuery  Shared filters applied to every variant
     * @param  array<int, array<string,mixed>>  $variantQueries  Variant-specific query arrays
     * @param  int     $pageSize        Page size per call (capped at API max)
     * @param  int     $concurrency     How many requests to fire in parallel per batch
     * @return array<int, array<string,mixed>>  Flat list of all rows
     */
    public function parallelFetch(
        string $path,
        array $baseQuery,
        array $variantQueries,
        int $pageSize = 25,
        int $concurrency = 2,
        int $interBatchDelayMs = 300,
    ): array {
        if (empty($variantQueries)) {
            return [];
        }

        $token = $this->token;
        $url   = $this->baseUrl . '/' . ltrim($path, '/');

        $rows = [];
        $pendingIdxs = array_keys($variantQueries);

        for ($attempt = 1; $attempt <= 3 && !empty($pendingIdxs); $attempt++) {
            if ($attempt === 2) {
                sleep(1);
            } elseif ($attempt === 3) {
                sleep(3);
            }

            $attemptIdxs = $pendingIdxs;
            $pendingIdxs = [];
            $batchIndex = 0;

            for ($start = 0; $start < count($attemptIdxs); $start += $concurrency) {
                if ($batchIndex > 0 && $interBatchDelayMs > 0) {
                    usleep($interBatchDelayMs * 1000);
                }
                $batchIndex++;

                $idxBatch = array_slice($attemptIdxs, $start, $concurrency);

                $responses = Http::pool(function (Pool $pool) use (
                    $idxBatch,
                    $variantQueries,
                    $baseQuery,
                    $pageSize,
                    $token,
                    $url
                ) {
                    $requests = [];
                    foreach ($idxBatch as $i) {
                        $request = $this->poolRequest($pool, "variant_{$i}", $token);
                        $query = $variantQueries[$i]
                            + ['page' => 0, 'size' => $pageSize, 'includeTotal' => 'false']
                            + $baseQuery;
                        $requests[] = $request->get($url, $query);
                    }
                    return $requests;
                });

                foreach ($idxBatch as $i) {
                    $resp = $responses["variant_{$i}"] ?? null;
                    if (!$resp instanceof Response) {
                        throw new CrmException(
                            "CRM API GET {$url} failed during parallel fetch.",
                            0,
                            ['errorCode' => 'POOL_ERROR', 'message' => "Missing pooled response for variant {$i}."],
                        );
                    }

                    if ($resp->status() === 429) {
                        $pendingIdxs[] = $i;
                        continue;
                    }

                    if (!$resp->successful()) {
                        $this->handle($resp, 'GET', $url);
                    }

                    foreach (($resp->json('data') ?? []) as $row) {
                        $rows[] = $row;
                    }
                }
            }
        }

        if (!empty($pendingIdxs)) {
            $this->throwRateLimited($path, 'parallelFetch', count($pendingIdxs));
        }

        return $rows;
    }

    /**
     * Return a clone of this client that uses the given Bearer token instead of
     * the default one loaded from config('crm.token').
     *
     * Used by the backoffice CRM browser to send per-center tokens stored on
     * the sites table. If $token is null/empty, the original token is kept.
     */
    public function withToken(?string $token): static
    {
        if (empty($token)) {
            return $this;
        }
        $clone = clone $this;
        $clone->token = $token;
        return $clone;
    }

    /**
     * Generic dispatch - kept protected to force callers through typed verbs.
     *
     * @param  array<string,mixed>  $query
     * @param  array<string,mixed>|null  $json
     * @return array<string,mixed>
     */
    protected function send(string $method, string $path, array $query = [], ?array $json = null): array
    {
        $this->assertConfigured();

        $url = $this->baseUrl . '/' . ltrim($path, '/');

        $request = $this->buildRequest();

        $options = ['query' => $query];
        if ($json !== null) {
            $options['json'] = $json;
        }

        try {
            $response = $request->send($method, $url, $options);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network-level failures (timeout, DNS, refused, SSL) - surface as a
            // typed CrmException so callers can catch a single exception type.
            $msg = "CRM API {$method} {$url} unreachable: " . $e->getMessage();
            if ($this->logEnabled) {
                Log::channel($this->logChannel)->warning($msg);
            }
            throw new CrmException($msg, 0, ['errorCode' => 'CONNECTION_ERROR', 'message' => $e->getMessage()], $e);
        }

        // Handle 429 (Too Many Requests) by honoring Retry-After and trying ONCE more.
        // If still 429, set a short cool-down flag so subsequent calls in the same
        // request fail-fast instead of piling on more requests.
        if ($response->status() === 429) {
            $cooldownKey = 'crm.rate_limited:' . substr((string) $this->token, -8);
            if (Cache::has($cooldownKey)) {
                throw new CrmException(
                    "CRM API rate limited (still in cool-down). Wait a minute and retry.",
                    429,
                    ['errorCode' => 'RATE_LIMITED', 'message' => 'In cool-down; not retrying.'],
                );
            }

            $retryAfter = (int) ($response->header('Retry-After') ?: 5);
            $retryAfter = max(1, min($retryAfter, 30));
            if ($this->logEnabled) {
                Log::channel($this->logChannel)->warning(
                    "CRM API 429 on {$path} - sleeping {$retryAfter}s and retrying once."
                );
            }
            sleep($retryAfter);

            try {
                $response = $request->send($method, $url, $options);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                throw new CrmException($e->getMessage(), 0, ['errorCode' => 'CONNECTION_ERROR']);
            }

            if ($response->status() === 429) {
                Cache::put($cooldownKey, true, 60);
                throw new CrmException(
                    "CRM API rate limited (retried once, still 429). Cool-down 60s.",
                    429,
                    ['errorCode' => 'RATE_LIMITED', 'message' => 'Still 429 after retry.'],
                );
            }
        }

        return $this->handle($response, $method, $url);
    }

    protected function buildRequest(): PendingRequest
    {
        $request = Http::withToken($this->token)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->retry(
                $this->retryTimes,
                $this->retrySleepMs,
                fn($exception) => $exception instanceof \Illuminate\Http\Client\ConnectionException,
                throw: false,
            );

        if (!$this->verifySsl) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * @return array<string,mixed>
     */
    protected function handle(Response $response, string $method, string $url): array
    {
        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $status = $response->status();
        $body   = $response->json();
        $msg    = "CRM API {$method} {$url} failed with {$status}";

        if ($this->logEnabled) {
            Log::channel($this->logChannel)->warning($msg, [
                'status' => $status,
                'body'   => $body,
            ]);
        }

        throw new CrmException($msg, $status, is_array($body) ? $body : null);
    }

    protected function assertConfigured(): void
    {
        if (empty($this->baseUrl)) {
            throw new CrmException('CRM_API_BASE_URL is not configured.');
        }
        if (empty($this->token)) {
            throw new CrmException('CRM_API_TOKEN is not configured. Set it in .env then run `php artisan config:clear`.');
        }
    }

    protected function poolRequest(Pool $pool, string $name, ?string $token)
    {
        $request = $pool->as($name)
            ->withToken($token)
            ->acceptJson()
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout);

        if (!$this->verifySsl) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * @param  array<int, array<string,mixed>>  $rows
     * @return array<int, array<string,mixed>>
     */
    protected function pagedScanSequentialFallback(
        string $path,
        array $baseQuery,
        int $pageSize,
        int $maxPages,
        array $rows,
    ): array {
        for ($page = 1; $page < $maxPages; $page++) {
            $payload = $this->send('GET', $path, query: [
                'page' => $page,
                'size' => $pageSize,
                'includeTotal' => 'false',
            ] + $baseQuery);

            $pageRows = is_array($payload['data'] ?? null) ? array_values($payload['data']) : [];
            foreach ($pageRows as $row) {
                $rows[] = $row;
            }

            $hasNext = $payload['pagination']['hasNext']
                ?? $payload['pagination']['hasMore']
                ?? null;

            if ($hasNext === false || count($pageRows) < $pageSize) {
                break;
            }
        }

        return $rows;
    }

    protected function throwRateLimited(string $path, string $operation, int $pendingCount): void
    {
        Cache::put(
            'crm.rate_limited:' . substr((string) $this->token, -8),
            true,
            60,
        );

        if ($this->logEnabled) {
            Log::channel($this->logChannel)->warning(
                "CRM API {$operation} exhausted 429 retries on {$path}; {$pendingCount} request(s) still pending. Entering 60s cool-down."
            );
        }

        throw new CrmException(
            "Trop de requêtes vers l'API CRM. Veuillez attendre 60 secondes.",
            429,
            [
                'errorCode' => 'RATE_LIMITED',
                'message' => "Trop de requêtes vers l'API CRM. Veuillez attendre 60 secondes.",
            ],
        );
    }
}
