<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmResyncLog extends Model
{
    protected $table = 'crm_resync_log';

    protected $fillable = [
        'user_id',
        'domain',
        'domain_label',
        'status',
        'crm_store_id',
        'steps',
        'error_message',
        'duration_seconds',
    ];

    protected $casts = [
        'steps' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'Système (auto)']);
    }
}
