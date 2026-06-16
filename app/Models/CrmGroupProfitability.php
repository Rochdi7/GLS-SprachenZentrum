<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CrmGroupProfitability extends Model
{
    protected $table = 'crm_group_profitability';

    protected $fillable = [
        'crm_class_id',
        'class_name',
        'crm_store_id',
        'site_name',
        'teacher_name',
        'level_name',
        'period_month',
        'period_type',
        'revenue',
        'paying_students',
        'teacher_salary',
        'salary_match_method',
        'other_expenses',
        'profit',
        'margin_pct',
        'attendance_rate',
        'total_sessions',
        'total_present',
        'total_absent',
        'active_students',
        'computed_at',
    ];

    protected $casts = [
        'revenue'        => 'decimal:2',
        'teacher_salary' => 'decimal:2',
        'other_expenses' => 'decimal:2',
        'profit'         => 'decimal:2',
        'margin_pct'     => 'decimal:2',
        'attendance_rate'=> 'decimal:2',
        'computed_at'    => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForStore(Builder $q, ?int $storeId): Builder
    {
        return $storeId ? $q->where('crm_store_id', $storeId) : $q;
    }

    public function scopeForMonth(Builder $q, string $month): Builder
    {
        return $q->where('period_month', $month)->where('period_type', 'monthly');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function marginColor(): string
    {
        if ($this->margin_pct < 0)  return 'danger';
        if ($this->margin_pct < 20) return 'warning';
        if ($this->margin_pct < 40) return 'info';
        return 'success';
    }

    public function attendanceWarning(): bool
    {
        return $this->attendance_rate !== null && $this->attendance_rate < 70;
    }
}
