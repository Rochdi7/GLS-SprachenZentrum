<?php

namespace App\Services\Crm;

use RuntimeException;
use Throwable;

class CrmException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?array $body = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $status ?? 0, $previous);
    }
}
