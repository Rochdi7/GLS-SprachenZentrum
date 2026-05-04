<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationRequest extends Model
{
    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'phone',
        'birth_date',
        'birth_place',
        'group_name',
        'level',
        'notes',
        'language',
        'status',
        'refusal_reason',
        'reviewed_at',
        'reviewed_by',
        'attestation_id',
    ];

    protected $casts = [
        'birth_date'  => 'date',
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REFUSED  = 'refused';

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attestation()
    {
        return $this->belongsTo(Attestation::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
