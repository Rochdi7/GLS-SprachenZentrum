<?php

namespace App\Exports;

use App\Services\Crm\Unified360Service;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * The Vue 360 workbook: sheet 1 = one row per paiement (full detail),
 * sheet 2 = one row per inscription with its payments aggregated.
 *
 * Both sheets honour the exact same filter bag as the on-screen table, so what
 * the user exports is always what the user was looking at.
 */
class Crm360Workbook implements WithMultipleSheets
{
    use Exportable;

    /** @param  array<string,mixed>  $filters */
    public function __construct(
        protected Unified360Service $service,
        protected array $filters = [],
    ) {}

    public function sheets(): array
    {
        return [
            new Crm360Export($this->service, $this->filters, Crm360Export::MODE_DETAIL),
            new Crm360Export($this->service, $this->filters, Crm360Export::MODE_SUMMARY),
        ];
    }
}
