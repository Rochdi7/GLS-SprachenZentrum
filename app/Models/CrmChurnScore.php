<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmChurnScore extends Model
{
    protected $fillable = [
        'crm_student_id',
        'crm_store_id',
        'score',
        'risk_level',
        'signals',
        'student_name',
        'registration_id',
        'class_id',
        'computed_at',
    ];

    protected $casts = [
        'signals'     => 'array',
        'computed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(CrmStudent::class, 'crm_student_id', 'crm_id');
    }

    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', ['high', 'critical']);
    }

    public function scopeForStore($query, ?int $storeId)
    {
        return $storeId ? $query->where('crm_store_id', $storeId) : $query;
    }
}
