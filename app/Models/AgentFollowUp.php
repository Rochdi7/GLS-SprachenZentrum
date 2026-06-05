<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentFollowUp extends Model
{
    protected $fillable = [
        'crm_student_id',
        'registration_id',
        'agent_id',
        'status',
        'note',
        'follow_up_date',
        'called_at',
        'created_by',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'called_at'      => 'datetime',
    ];

    public const STATUSES = [
        'pending'        => 'En attente',
        'contacted'      => 'Contacté',
        'no_answer'      => 'Pas de réponse',
        'interested'     => 'Intéressé',
        'not_interested' => 'Pas intéressé',
        'solved'         => 'Résolu',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('crm_student_id', $studentId);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('follow_up_date', today());
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
