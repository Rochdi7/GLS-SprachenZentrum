<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmRegistration extends Model
{
    protected $fillable = [
        'crm_id',
        'crm_student_id',
        'crm_class_id',
        'crm_store_id',
        'status',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(CrmStudent::class, 'crm_student_id', 'crm_id');
    }

    public function class()
    {
        return $this->belongsTo(CrmClass::class, 'crm_class_id', 'crm_id');
    }
}
