<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tracks step-level progress for crm:sync-all.
 *
 * One row per step name (unique). CrmSyncAllCommand updates the row as
 * the step transitions: pending → running → done | failed.
 *
 * Used by the --resume flag to skip steps already completed today.
 *
 * Manual inspection:
 *   SELECT step, status, completed_at, last_error FROM crm_sync_log ORDER BY id;
 */
class CrmSyncLog extends Model
{
    protected $table = 'crm_sync_log';

    protected $fillable = [
        'step',
        'status',
        'records_synced',
        'attempts',
        'last_error',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function isCompletedToday(): bool
    {
        return $this->status === 'done'
            && $this->completed_at !== null
            && $this->completed_at->isToday();
    }
}
