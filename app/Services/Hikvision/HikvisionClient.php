<?php

namespace App\Services\Hikvision;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class HikvisionClient
{
    protected string $baseUrl;

    protected ?string $apiKey;

    protected ?string $apiSecret;

    protected int $timeout;

    public function __construct(?array $config = null)
    {
        $config ??= config('hikvision');

        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->apiKey = $config['api_key'] ?? null;
        $this->apiSecret = $config['api_secret'] ?? null;
        $this->timeout = (int) ($config['timeout'] ?? 15);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && filled($this->apiKey) && filled($this->apiSecret);
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function apiKey(): ?string
    {
        return $this->apiKey;
    }

    public function apiSecret(): ?string
    {
        return $this->apiSecret;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    public function pendingRequest(): PendingRequest
    {
        return Http::acceptJson()
            ->baseUrl($this->baseUrl)
            ->timeout($this->timeout);
    }

    /**
     * Central request entrypoint for future Hikvision endpoints.
     *
     * Authentication is intentionally not hardcoded here because Hikvision
     * deployments can vary (digest auth, signature headers, gateway proxy).
     * Higher-level services should attach the correct auth material through the
     * $headers / $options arguments while secrets remain inside config().
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function send(
        string $method,
        string $path,
        array $query = [],
        array $payload = [],
        array $headers = [],
        array $options = [],
    ): array {
        /** @var Response $response */
        $response = $this->pendingRequest()
            ->withHeaders($headers)
            ->send($method, '/' . ltrim($path, '/'), array_filter([
                'query' => $query ?: null,
                'json' => $payload ?: null,
            ] + $options));

        $response->throw();

        return $response->json() ?? [];
    }
}
