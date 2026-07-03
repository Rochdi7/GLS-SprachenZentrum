<?php

namespace App\Models\Hikvision;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HikvisionPerson extends Model
{
    protected $table = 'hikvision_persons';

    protected $fillable = [
        'external_id',
        'employee_no',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'department',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(HikvisionAttendance::class, 'hikvision_person_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
