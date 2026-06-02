<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CrmDailyReport extends Model
{
    protected $fillable = [
        'report_date',
        'payload',
        'revenue_yesterday',
        'new_registrations',
        'outstanding_receivables',
        'students_at_risk',
        'best_center',
        'generated_at',
        'email_sent_at',
    ];

    protected $casts = [
        'payload'                 => 'array',
        'report_date'             => 'date',
        'generated_at'            => 'datetime',
        'email_sent_at'           => 'datetime',
        'revenue_yesterday'       => 'decimal:2',
        'outstanding_receivables' => 'decimal:2',
    ];

    /**
     * Scope to order by report_date descending.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('report_date', 'desc');
    }

    /**
     * Return the most recent report.
     */
    public static function latestReport(): ?static
    {
        return static::query()->orderBy('report_date', 'desc')->first();
    }
}
