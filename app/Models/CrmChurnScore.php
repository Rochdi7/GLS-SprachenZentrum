<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'score'       => 'integer',
    ];

    /**
     * Risk level boundaries: level => [min, max] (inclusive).
     */
    const RISK_LEVELS = [
        'low'      => [0,  30],
        'medium'   => [31, 55],
        'high'     => [56, 75],
        'critical' => [76, 100],
    ];

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('risk_level', 'critical');
    }

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->where('score', '>=', 56);
    }
}
