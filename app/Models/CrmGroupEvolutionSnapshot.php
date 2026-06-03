<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Precomputed group evolution buckets per class per date range.
 *
 * Written by: php artisan crm:build-group-evolution --all
 * Read by:    GroupEvolutionService::build()
 *
 * Each row holds the five evolution metrics for one class over a given range:
 *   debuts      — founding students (first payment month = class start month)
 *   ajouts      — late arrivals (joined after class started)
 *   quittants   — departed students (stopped paying, not a transfer)
 *   changements — transferred students (archived in this class, active elsewhere)
 *   actifs      — current active count (from crm_classes.raw_data)
 *
 * The dashboard query:
 *   SELECT * FROM crm_group_evolution_snapshot
 *   WHERE crm_store_id = ?
 *     AND range_start <= ? AND range_end >= ?
 *   ORDER BY class_name
 */
class CrmGroupEvolutionSnapshot extends Model
{
    protected $table = 'crm_group_evolution_snapshot';

    protected $fillable = [
        'crm_store_id',
        'class_id',
        'class_name',
        'class_start_date',
        'class_end_date',
        'class_start_month',
        'debuts',
        'ajouts',
        'quittants',
        'changements',
        'actifs',
        'range_start',
        'range_end',
        'computed_at',
    ];

    protected $casts = [
        'class_start_date' => 'date',
        'class_end_date'   => 'date',
        'range_start'      => 'date',
        'range_end'        => 'date',
        'computed_at'      => 'datetime',
    ];
}
