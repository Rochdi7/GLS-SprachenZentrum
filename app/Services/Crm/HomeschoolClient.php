<?php

namespace App\Services\Crm;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Low-level HTTP client for the Homeschool External API v1.
 *
 * Holds the Bearer token, base URL, retry and timeout policy. All resource
 * classes go through this — never call Http::get() directly elsewhere in the
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
            \Illuminate\Support\Facades\Cache::forget($key);
        }

        return \Illuminate\Support\Facades\Cache::remember($key, $ttl, function () use ($path, $query) {
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
     * in the base query — Homeschool's COUNT query is the slowest part.
     *
     * @param  array<string,mixed>  $baseQuery  Filters (excluding page/size)
     * @return array<int, array<string,mixed>>  Flat list of all rows
     */


    /**
     * Fire one parallel GET per query-variant and merge their `data` arrays.
     *
     * Use this when the API doesn't support a range filter (e.g. session-presence
     * only accepts a single `date`) but you need data across many values of that
     * filter. Each variant becomes its own HTTP call, fanned out 10-at-a-time.
     *
     * @param  string  $path          API path (e.g. /api/external/v1/session-presence)
     * @param  array   $baseQuery     Shared filters applied to every variant
     * @param  array   $variantQueries  List of variant-specific query arrays
     *                                  (e.g. [['date' => '2026-05-01'], ['date' => '2026-05-02'], ...])
     * @param  int     $pageSize      Page size per call (capped at API max)
     * @param  int     $concurrency   How many requests to fire in parallel per batch
     * @return array<int, array<string,mixed>>  Flat list of all rows
     */
    public function parallelFetch(
        string $path,
        array $baseQuery,
        array $variantQueries,
        int $pageSize = 25,
        int $concurrency = 3,
        int $interBatchDelayMs = 250,
    ): array {
        if (empty($variantQueries)) {
            return [];
        }

        $token   = $this->token;
        $baseUrl = $this->baseUrl;
        $verify  = $this->verifySsl;
        $timeout = $this->timeout;
        $url     = $baseUrl . '/' . ltrim($path, '/');

        $rows = [];
        $batchIndex = 0;

        for ($start = 0; $start < count($variantQueries); $start += $concurrency) {
            // Throttle between batches so we don't trigger the upstream rate
            // limiter. First batch fires immediately; subsequent batches wait.
            if ($batchIndex > 0 && $interBatchDelayMs > 0) {
                usleep($interBatchDelayMs * 1000);
            }
            $batchIndex++;

            $end = min($start + $concurrency, count($variantQueries));

            $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use (
                $start, $end, $variantQueries, $baseQuery, $pageSize, $token, $url, $verify, $timeout,
            ) {
                $requests = [];
                for ($i = $start; $i < $end; $i++) {
                    $r = $pool->as("variant_{$i}")
                        ->withToken($token)
                        ->acceptJson()
                        ->timeout($timeout);
                    if (!$verify) {
                        $r = $r->withoutVerifying();
                    }
                    // Order matters: variant overrides defaults, base fills the rest.
                    // The `+` operator keeps LEFT keys on collision.
                    $query = $variantQueries[$i]
                        + ['page' => 0, 'size' => $pageSize, 'includeTotal' => 'false']
                        + $baseQuery;
                    $requests[] = $r->get($url, $query);
                }
                return $requests;
            });

            $batchHit429 = false;
            for ($i = $start; $i < $end; $i++) {
                $resp = $responses["variant_{$i}"] ?? null;
                if ($resp instanceof Response) {
                    if ($resp->status() === 429) {
                        $batchHit429 = true;
                        continue;
                    }
                    if ($resp->successful()) {
                        foreach (($resp->json('data') ?? []) as $row) {
                            $rows[] = $row;
                        }
                    }
                }
            }

            // If any request in the batch was rate-limited, set the global
            // cool-down so the next batches and any other callers fail-fast
            // instead of piling on more rejected requests.
            if ($batchHit429) {
                \Illuminate\Support\Facades\Cache::put(
                    'crm.rate_limited:' . substr((string) $this->token, -8),
                    true,
                    60,
                );
                if ($this->logEnabled) {
                    Log::channel($this->logChannel)->warning(
                        "CRM API parallelFetch batch hit 429 on {$path} — entering 60s cool-down."
                    );
                }
                break; // stop firing further batches
            }
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
     * Generic dispatch — kept protected to force callers through typed verbs.
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
            // Network-level failures (timeout, DNS, refused, SSL) — surface as a
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
            if (\Illuminate\Support\Facades\Cache::has($cooldownKey)) {
                throw new CrmException(
                    "CRM API rate limited (still in cool-down). Wait a minute and retry.",
                    429,
                    ['errorCode' => 'RATE_LIMITED', 'message' => 'In cool-down; not retrying.'],
                );
            }

            $retryAfter = (int) ($response->header('Retry-After') ?: 5);
            $retryAfter = max(1, min($retryAfter, 30)); // clamp to [1, 30] seconds
            if ($this->logEnabled) {
                Log::channel($this->logChannel)->warning(
                    "CRM API 429 on {$path} — sleeping {$retryAfter}s and retrying once."
                );
            }
            sleep($retryAfter);

            try {
                $response = $request->send($method, $url, $options);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                throw new CrmException($e->getMessage(), 0, ['errorCode' => 'CONNECTION_ERROR']);
            }

            if ($response->status() === 429) {
                // Still rate limited — set cool-down for 60s so other calls
                // in this request batch fail-fast instead of stacking more 429s.
                \Illuminate\Support\Facades\Cache::put($cooldownKey, true, 60);
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
                fn ($exception) => $exception instanceof \Illuminate\Http\Client\ConnectionException,
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
}
