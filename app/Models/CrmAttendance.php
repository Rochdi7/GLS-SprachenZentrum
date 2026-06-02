<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmAttendance extends Model
{
    protected $table = 'crm_attendance';

    protected $fillable = [
        'crm_id',
        'crm_class_id',
        'crm_student_id',
        'date',
        'is_present',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'date' => 'date',
        'is_present' => 'boolean',
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(CrmClass::class, 'crm_class_id', 'crm_id');
    }

    public function student()
    {
        return $this->belongsTo(CrmStudent::class, 'crm_student_id', 'crm_id');
    }
}
