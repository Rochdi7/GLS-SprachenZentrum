<?php

namespace App\Models\Hikvision;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HikvisionDevice extends Model
{
    protected $table = 'hikvision_devices';

    protected $fillable = [
        'external_id',
        'name',
        'serial_number',
        'ip_address',
        'status',
        'firmware_version',
        'last_seen_at',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(HikvisionAttendance::class, 'hikvision_device_id');
    }

    public function alarms(): HasMany
    {
        return $this->hasMany(HikvisionAlarm::class, 'hikvision_device_id');
    }
}
