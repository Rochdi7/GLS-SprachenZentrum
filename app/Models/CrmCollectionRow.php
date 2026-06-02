<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmCollectionRow extends Model
{
    protected $fillable = [
        'crm_id',
        'crm_store_id',
        'student_id',
        'student_name',
        'store_name',
        'total_price',
        'rest_amount',
        'due_date',
        'payment_delay_days',
        'registration_id',
        'registration_status_id',
        'registration_status_name',
        'service_type_name',
        'class_id',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'raw_data'       => 'array',
        'due_date'       => 'date:Y-m-d',
        'last_synced_at' => 'datetime',
        'rest_amount'    => 'decimal:2',
        'total_price'    => 'decimal:2',
    ];
}
