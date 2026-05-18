<?php

namespace App\Services\Crm;

use App\Services\Crm\Resources\Groups;
use App\Services\Crm\Resources\Lov;
use App\Services\Crm\Resources\Payments;
use App\Services\Crm\Resources\Registrations;
use App\Services\Crm\Resources\Students;

/**
 * Entry point for the Homeschool CRM integration.
 *
 * Usage:
 *
 *   $crm = app(\App\Services\Crm\Crm::class);
 *   $crm->students()->list(['page' => 1, 'size' => 50]);
 *   $crm->lov()->banks();
 *   $crm->payments()->checks();
 *
 * Resources are lazily instantiated and shared per Crm instance.
 */
class Crm
{
    protected array $resources = [];

    public function __construct(protected HomeschoolClient $client)
    {
    }

    public function students(): Students
    {
        return $this->resources[Students::class] ??= new Students($this->client);
    }

    public function registrations(): Registrations
    {
        return $this->resources[Registrations::class] ??= new Registrations($this->client);
    }

    public function payments(): Payments
    {
        return $this->resources[Payments::class] ??= new Payments($this->client);
    }

    public function groups(): Groups
    {
        return $this->resources[Groups::class] ??= new Groups($this->client);
    }

    public function lov(): Lov
    {
        return $this->resources[Lov::class] ??= new Lov($this->client);
    }

    public function client(): HomeschoolClient
    {
        return $this->client;
    }

    /**
     * Return a new Crm bound to a per-center token (overrides config('crm.token')).
     * Pass null/empty to keep the default token.
     */
    public function withToken(?string $token): self
    {
        if (empty($token)) {
            return $this;
        }
        return new self($this->client->withToken($token));
    }
}
