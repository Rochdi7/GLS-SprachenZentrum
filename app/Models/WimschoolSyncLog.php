<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WimschoolSyncLog extends Model
{
    protected $fillable = [
        'group_id',
        'date_start',
        'date_end',
        'status',
        'error_message',
        'records_synced',
        'created_by',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
