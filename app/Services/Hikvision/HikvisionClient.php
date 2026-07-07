<?php

namespace App\Services\Hikvision;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class HikvisionClient
{
    protected string $baseUrl;

    protected ?string $username;

    protected ?string $password;

    protected int $timeout;

    public function __construct(?array $config = null)
    {
        $config ??= config('hikvision');

        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->username = $config['username'] ?? null;
        $this->password = $config['password'] ?? null;
        $this->timeout = (int) ($config['timeout'] ?? 15);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && filled($this->username) && filled($this->password);
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function username(): ?string
    {
        return $this->username;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    public function pendingRequest(): PendingRequest
    {
        return Http::acceptJson()
            ->baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withDigestAuth((string) $this->username, (string) $this->password);
    }

    /**
     * Central request entrypoint for ISAPI endpoints (device is authenticated
     * via Digest Auth on every request, see pendingRequest()).
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
