<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Monthly per-class attendance aggregate.
 *
 * Written by: php artisan crm:build-presence-summary --all
 * Read by:    PresenceSuiviService::allTimeTotals()
 *
 * One row per (crm_class_id, month). Replaces the CarbonPeriod PHP loop
 * that previously iterated 700+ days × 30 classes per request.
 *
 * The dashboard query for all-time totals:
 *   SELECT SUM(saisie_sessions), SUM(draft_sessions)
 *   FROM crm_presence_summary
 *   WHERE crm_store_id = ?  -- optional
 */
class CrmPresenceSummary extends Model
{
    protected $table = 'crm_presence_summary';

    protected $fillable = [
        'crm_class_id',
        'crm_store_id',
        'class_name',
        'teacher_name',
        'month',
        'saisie_sessions',
        'draft_sessions',
        'expected_sessions',
        'missing_sessions',
        'total_present',
        'total_absent',
        'total_students',
        'computed_at',
    ];

    protected $casts = [
        'month'       => 'date',
        'computed_at' => 'datetime',
    ];
}
