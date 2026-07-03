<?php

namespace App\Models\Hikvision;

use Illuminate\Database\Eloquent\Model;

class HikvisionSyncLog extends Model
{
    protected $table = 'hikvision_sync_logs';

    protected $fillable = [
        'channel',
        'action',
        'status',
        'records_total',
        'records_success',
        'records_failed',
        'error_message',
        'started_at',
        'completed_at',
        'context',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'context' => 'array',
    ];
}
