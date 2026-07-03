<?php

namespace App\Models\Hikvision;

use Illuminate\Database\Eloquent\Model;

class HikvisionWebhookEvent extends Model
{
    protected $table = 'hikvision_webhook_events';

    protected $fillable = [
        'event_uuid',
        'event_type',
        'source',
        'signature_masked',
        'payload',
        'received_at',
        'processed_at',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
