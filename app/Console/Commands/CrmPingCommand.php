<?php

namespace App\Console\Commands;

use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Illuminate\Console\Command;

class CrmPingCommand extends Command
{
    protected $signature = 'crm:ping {--endpoint=banks : LOV endpoint to test (banks, categories, payment-methods, ...)}';

    protected $description = 'Verify connectivity to the Wimschool External API using a LOV endpoint.';

    public function handle(Crm $crm): int
    {
        $endpoint = $this->option('endpoint');

        $method = match ($endpoint) {
            'banks'                => 'banks',
            'categories'           => 'categories',
            'payment-methods'      => 'paymentMethods',
            'payment-types'        => 'paymentTypes',
            'payment-statuses'     => 'paymentStatuses',
            'school-levels'        => 'schoolLevels',
            'registration-statuses' => 'registrationStatuses',
            default => null,
        };

        if (!$method) {
            $this->error("Unknown endpoint '{$endpoint}'. Try one of: banks, categories, payment-methods, payment-types, payment-statuses, school-levels, registration-statuses.");
            return self::FAILURE;
        }

        $this->info("Calling LOV /{$endpoint} ...");

        try {
            $data = $crm->lov()->{$method}();
        } catch (CrmException $e) {
            $this->error('CRM call failed: ' . $e->getMessage());
            if ($e->body) {
                $this->line(json_encode($e->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            return self::FAILURE;
        }

        $count = is_array($data) ? (isset($data[0]) ? count($data) : count($data, COUNT_RECURSIVE)) : 0;
        $this->info("OK — received payload (top-level keys: " . (is_array($data) ? implode(', ', array_keys($data)) : 'n/a') . ")");
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
