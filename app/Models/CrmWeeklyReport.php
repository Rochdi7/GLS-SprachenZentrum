<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CrmWeeklyReport extends Model
{
    protected $fillable = [
        'week_start',
        'week_end',
        'week_label',
        'total_revenue',
        'new_registrations',
        'active_students',
        'outstanding_receivables',
        'best_center',
        'centers_ranking',
        'daily_breakdown',
        'payload',
        'generated_at',
    ];

    protected $casts = [
        'week_start'              => 'date',
        'week_end'                => 'date',
        'total_revenue'           => 'decimal:2',
        'outstanding_receivables' => 'decimal:2',
        'centers_ranking'         => 'array',
        'daily_breakdown'         => 'array',
        'payload'                 => 'array',
        'generated_at'            => 'datetime',
    ];

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('week_start', 'desc');
    }

    public static function latestReport(): ?static
    {
        return static::query()->orderBy('week_start', 'desc')->first();
    }

    /**
     * Return the ISO week start (Monday) for any given date.
     */
    public static function weekStartFor(Carbon $date): Carbon
    {
        return $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }
}
