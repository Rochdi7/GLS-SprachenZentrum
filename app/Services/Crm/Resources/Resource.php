<?php

namespace App\Services\Crm\Resources;

use App\Services\Crm\WimschoolClient;

abstract class Resource
{
    public function __construct(protected WimschoolClient $client)
    {
    }
}
