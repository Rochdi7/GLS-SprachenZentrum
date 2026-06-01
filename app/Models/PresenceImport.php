<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents one attendance Excel import snapshot for a group.
 * Each import is a versioned snapshot — never overwritten.
 */
class PresenceImport extends Model
{
    protected $fillable = [
        'group_id',
        'version',
        'month',
        'date_start',
        'date_end',
        'total_days',
        'payment_per_student',
        'weekly_threshold',
        'weekly_rate_percent',
        'file_name',
        'file_path',
        'notes',
        'imported_by',
        'is_crm_api',
    ];

    protected $casts = [
        'month' => 'date',
        'date_start' => 'date',
        'date_end' => 'date',
        'payment_per_student' => 'decimal:2',
        'weekly_rate_percent' => 'decimal:2',
        'is_crm_api' => 'boolean',
    ];

    public const DEFAULT_WEEKLY_THRESHOLD = 3;

    public const DEFAULT_WEEKLY_RATE_PERCENT = 25.00;

    /**
     * Per-week unit amount: base_price * weekly_rate_percent / 100
     */
    public function getWeeklyUnitAmount(): float
    {
        $base = (float) ($this->getEffectivePaymentPerStudent() ?? 0);
        $pct = (float) ($this->weekly_rate_percent ?? self::DEFAULT_WEEKLY_RATE_PERCENT);

        return round($base * $pct / 100, 2);
    }

    public function getThreshold(): int
    {
        return (int) ($this->weekly_threshold ?? self::DEFAULT_WEEKLY_THRESHOLD);
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function students()
    {
        return $this->hasMany(PresenceImportStudent::class);
    }

    public function paymentSummary()
    {
        return $this->hasOne(PresencePaymentSummary::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Get the effective payment rate (import override or teacher default).
     */
    public function getEffectivePaymentPerStudent(): ?float
    {
        if ($this->payment_per_student !== null) {
            return (float) $this->payment_per_student;
        }

        return $this->group?->teacher?->payment_per_student
            ? (float) $this->group->teacher->payment_per_student
            : null;
    }

    /**
     * Human-readable weeks + extra days label (e.g. "4 semaines + 1 jour").
     */
    public function getTotalWeeksLabelAttribute(): string
    {
        $weeks = intdiv($this->total_days, 5);
        $days = $this->total_days % 5;

        if ($days === 0) {
            return $weeks.' semaine'.($weeks > 1 ? 's' : '');
        }

        if ($weeks === 0) {
            return $days.' jour'.($days > 1 ? 's' : '');
        }

        return $weeks.' semaine'.($weeks > 1 ? 's' : '').' + '.$days.' jour'.($days > 1 ? 's' : '');
    }

    /**
     * Get the previous import version for the same group.
     */
    public function previousVersion(): ?self
    {
        return static::where('group_id', $this->group_id)
            ->where('version', '<', $this->version)
            ->orderByDesc('version')
            ->first();
    }
}
