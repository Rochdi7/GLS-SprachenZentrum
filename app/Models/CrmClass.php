<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmClass extends Model
{
    protected $fillable = [
        'crm_id',
        'class_id',
        'name',
        'crm_teacher_id',
        'level',
        'site_id',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function registrations()
    {
        return $this->hasMany(CrmRegistration::class, 'crm_class_id', 'crm_id');
    }

    public function attendance()
    {
        return $this->hasMany(CrmAttendance::class, 'crm_class_id', 'crm_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
