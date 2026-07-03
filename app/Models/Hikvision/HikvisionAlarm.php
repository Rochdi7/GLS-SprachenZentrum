<?php

namespace App\Models\Hikvision;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HikvisionAlarm extends Model
{
    protected $table = 'hikvision_alarms';

    protected $fillable = [
        'external_id',
        'hikvision_device_id',
        'device_external_id',
        'alarm_type',
        'severity',
        'status',
        'triggered_at',
        'resolved_at',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(HikvisionDevice::class, 'hikvision_device_id');
    }
}
