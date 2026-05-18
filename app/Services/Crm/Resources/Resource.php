<?php

namespace App\Services\Crm\Resources;

use App\Services\Crm\HomeschoolClient;

abstract class Resource
{
    public function __construct(protected HomeschoolClient $client)
    {
    }
}
