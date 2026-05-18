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
     * @param  array<string,mixed>  $query
     * @return array<string,mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->send('GET', $path, query: $query);
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
