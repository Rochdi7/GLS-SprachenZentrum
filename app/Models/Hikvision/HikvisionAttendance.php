<?php

namespace App\Models\Hikvision;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HikvisionAttendance extends Model
{
    protected $table = 'hikvision_attendance_records';

    protected $fillable = [
        'external_id',
        'hikvision_device_id',
        'hikvision_person_id',
        'device_external_id',
        'person_external_id',
        'direction',
        'verification_mode',
        'status',
        'occurred_at',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(HikvisionDevice::class, 'hikvision_device_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(HikvisionPerson::class, 'hikvision_person_id');
    }
}
