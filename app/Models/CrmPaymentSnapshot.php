<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmPaymentSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'crm_payment_id',
        'snapshot_date',
        'crm_store_id',
        'student_id',
        'registration_id',
        'reference',
        'amount',
        'rest_amount',
        'effective_date',
        'due_date',
        'payment_method_id',
        'payment_method_name',
        'payment_type_id',
        'payment_type_name',
        'user_creation_id',
        'user_creation_full_name',
        'user_update_id',
        'user_update_full_name',
        'date_creation',
        'date_update',
        'date_creation_date',
        'payload',
        'payload_hash',
    ];

    protected $casts = [
        'snapshot_date'      => 'date',
        'effective_date'     => 'date',
        'due_date'           => 'date',
        'date_creation'      => 'datetime',
        'date_update'        => 'datetime',
        'date_creation_date' => 'date',
        'amount'             => 'decimal:2',
        'rest_amount'        => 'decimal:2',
        'payload'            => 'array',
    ];
}
