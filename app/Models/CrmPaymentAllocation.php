<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Local mirror of /api/external/v1/bulk/payment-allocations.
 *
 * One row per allocation record from the Wimschool API.
 * Synced by: php artisan crm:sync-payment-allocations --all
 *
 * This table is the data source for BuildGroupEvolutionCommand.
 * Having it locally eliminates the 40 live API calls that GroupEvolutionService
 * previously made during each dashboard page load.
 */
class CrmPaymentAllocation extends Model
{
    protected $fillable = [
        'crm_id',
        'crm_store_id',
        'student_id',
        'class_id',
        'service_type_name',
        'is_inscription',
        'allocation_date',
        'allocation_month',
        'payment_date',
        'amount',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'allocation_date'  => 'date',
        'payment_date'     => 'date',
        'amount'           => 'decimal:2',
        'is_inscription'   => 'boolean',
        'raw_data'         => 'array',
        'last_synced_at'   => 'datetime',
    ];
}
