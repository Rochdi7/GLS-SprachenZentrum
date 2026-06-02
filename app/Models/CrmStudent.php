<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmStudent extends Model
{
    protected $fillable = [
        'crm_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function registrations()
    {
        return $this->hasMany(CrmRegistration::class, 'crm_student_id', 'crm_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
