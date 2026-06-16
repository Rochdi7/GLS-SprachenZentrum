<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CrmAlert extends Model
{
    protected $table = 'crm_alerts';

    protected $fillable = [
        'crm_store_id',
        'alert_type',
        'severity',
        'title',
        'message',
        'payload',
        'crm_student_id',
        'crm_class_id',
        'status',
        'resolved_by',
        'resolved_at',
        'notes',
        'dedup_key',
        'detected_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'resolved_at' => 'datetime',
        'detected_at' => 'datetime',
    ];

    // ── Severity helpers ─────────────────────────────────────────────────────

    public const SEVERITIES = ['low', 'medium', 'high', 'critical'];

    public const SEVERITY_COLORS = [
        'low'      => 'secondary',
        'medium'   => 'warning',
        'high'     => 'danger',
        'critical' => 'danger',
    ];

    public const ALERT_TYPE_LABELS = [
        'absent_student'  => 'Absent 3×',
        'unpaid_30d'      => 'Impayé >30j',
        'cheque_due_soon' => 'Chèque proche',
        'weak_attendance' => 'Présence faible',
        'group_near_end'  => 'Groupe en fin de vie',
    ];

    public function severityColor(): string
    {
        return self::SEVERITY_COLORS[$this->severity] ?? 'secondary';
    }

    public function typeLabel(): string
    {
        return self::ALERT_TYPE_LABELS[$this->alert_type] ?? $this->alert_type;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', 'open');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeForStore(Builder $q, ?int $storeId): Builder
    {
        return $storeId ? $q->where('crm_store_id', $storeId) : $q;
    }

    public function scopeOfType(Builder $q, ?string $type): Builder
    {
        return $type ? $q->where('alert_type', $type) : $q;
    }

    public function scopeOfSeverity(Builder $q, ?string $severity): Builder
    {
        return $severity ? $q->where('severity', $severity) : $q;
    }
}
