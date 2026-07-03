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
        'month_label',
        'crm_teacher_name',
        // Payment mode + lifecycle
        'payment_mode',
        'status',
        'calculation_version',
        // Period mode
        'attached_month',
        'attached_year',
        'group_month_number',
        'base_price',
        'period_tiers_json',
        // Hourly mode
        'hourly_rate',
        'total_hours',
        'performance_bonus',
        'final_total',
        // Payment info + lifecycle audit
        'payment_date',
        'payment_method',
        'payment_reference',
        'payment_notes',
        'validated_by',
        'validated_at',
        'paid_by',
        'paid_at',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'month' => 'date',
        'date_start' => 'date',
        'date_end' => 'date',
        'payment_per_student' => 'decimal:2',
        'weekly_rate_percent' => 'decimal:2',
        'is_crm_api' => 'boolean',
        'period_tiers_json' => 'array',
        'base_price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'final_total' => 'decimal:2',
        'payment_date' => 'date',
        'validated_at' => 'datetime',
        'paid_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public const DEFAULT_WEEKLY_THRESHOLD = 3;

    public const DEFAULT_WEEKLY_RATE_PERCENT = 25.00;

    /* ------------------------------------------------------------------ */
    /*  Payment mode + lifecycle status                                    */
    /* ------------------------------------------------------------------ */

    public const MODE_WEEKLY = 'weekly';

    public const MODE_PERIOD = 'period';

    public const MODE_HOURLY = 'hourly';

    public const MODES = [self::MODE_WEEKLY, self::MODE_PERIOD, self::MODE_HOURLY];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_VALIDATED = 'validated';

    public const STATUS_PAID = 'paid';

    public const STATUS_LOCKED = 'locked';

    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_VALIDATED, self::STATUS_PAID, self::STATUS_LOCKED];

    /** Statuses that freeze the import against any further edits. */
    public const IMMUTABLE_STATUSES = [self::STATUS_PAID, self::STATUS_LOCKED];

    public function isWeekly(): bool
    {
        // Null-safe: legacy rows created before this column existed are weekly.
        return ($this->payment_mode ?? self::MODE_WEEKLY) === self::MODE_WEEKLY;
    }

    public function isPeriod(): bool
    {
        return $this->payment_mode === self::MODE_PERIOD;
    }

    public function isHourly(): bool
    {
        return $this->payment_mode === self::MODE_HOURLY;
    }

    /** Lifecycle removed — imports are never locked against editing. */
    public function isLocked(): bool
    {
        return false;
    }

    /* ------------------------------------------------------------------ */
    /*  Payment methods                                                    */
    /* ------------------------------------------------------------------ */

    public const PAY_CASH     = 'cash';
    public const PAY_TRANSFER = 'bank_transfer';
    public const PAY_CHECK    = 'check';
    public const PAY_OTHER    = 'other';

    public const PAYMENT_METHODS = [self::PAY_CASH, self::PAY_TRANSFER, self::PAY_CHECK, self::PAY_OTHER];

    public const PAYMENT_METHOD_LABELS = [
        self::PAY_CASH     => 'Espèces',
        self::PAY_TRANSFER => 'Virement bancaire',
        self::PAY_CHECK    => 'Chèque',
        self::PAY_OTHER    => 'Autre',
    ];

    public const STATUS_LABELS = [
        self::STATUS_DRAFT     => 'Brouillon',
        self::STATUS_VALIDATED => 'Validé',
        self::STATUS_PAID      => 'Payé',
        self::STATUS_LOCKED    => 'Verrouillé',
    ];

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function paymentMethodLabel(): ?string
    {
        return $this->payment_method ? (self::PAYMENT_METHOD_LABELS[$this->payment_method] ?? $this->payment_method) : null;
    }

    /* ------------------------------------------------------------------ */
    /*  Lifecycle state machine                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Allowed forward/back transitions for a normal (non-Super-Admin) user.
     *
     *   draft     → validated
     *   validated → paid | draft (return to draft)
     *   paid      → locked
     *   locked    → (nothing — Super Admin only, handled separately)
     */
    public const TRANSITIONS = [
        self::STATUS_DRAFT     => [self::STATUS_VALIDATED],
        self::STATUS_VALIDATED => [self::STATUS_PAID, self::STATUS_DRAFT],
        self::STATUS_PAID      => [self::STATUS_LOCKED],
        self::STATUS_LOCKED    => [],
    ];

    public function isDraft(): bool     { return $this->status === self::STATUS_DRAFT; }
    public function isValidated(): bool { return $this->status === self::STATUS_VALIDATED; }
    public function isPaid(): bool      { return $this->status === self::STATUS_PAID; }
    public function isFullyLocked(): bool { return $this->status === self::STATUS_LOCKED; }

    /**
     * Can this import legally move to $target?
     *
     * Super Admin may roll a locked import back (accounting correction); no one
     * else can leave LOCKED. All other transitions follow the matrix.
     */
    public function canTransitionTo(string $target, ?User $user = null): bool
    {
        if (! in_array($target, self::STATUSES, true)) {
            return false;
        }

        // Super Admin escape hatch: can roll back from locked to any earlier state.
        if ($this->status === self::STATUS_LOCKED) {
            return $user?->hasRole('Super Admin') === true
                && in_array($target, [self::STATUS_PAID, self::STATUS_VALIDATED, self::STATUS_DRAFT], true);
        }

        return in_array($target, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /* ---- Capability gates ------------------------------------------- */
    // The status lifecycle was removed: payments are managed by admins/super
    // admins only, so imports are ALWAYS editable and deletable. These helpers
    // now always allow the action (columns kept for backward compatibility).

    public function canEditAmounts(): bool { return true; }

    public function canRecalculate(): bool { return true; }

    public function canOverride(): bool { return true; }

    public function canDelete(?User $user = null): bool { return true; }

    /* ------------------------------------------------------------------ */
    /*  Lifecycle audit relations                                          */
    /* ------------------------------------------------------------------ */

    public function statusLogs()
    {
        return $this->hasMany(PayrollStatusLog::class)->orderByDesc('created_at');
    }

    public function validatedBy() { return $this->belongsTo(User::class, 'validated_by'); }
    public function paidBy()      { return $this->belongsTo(User::class, 'paid_by'); }
    public function lockedBy()    { return $this->belongsTo(User::class, 'locked_by'); }

    /* ------------------------------------------------------------------ */
    /*  Period-mode helpers (frozen, reproducible)                         */
    /* ------------------------------------------------------------------ */

    /**
     * The tier table frozen on this import at creation. Falls back to the
     * service default only if somehow absent (defensive).
     */
    public function getFrozenTiers(): array
    {
        $tiers = $this->period_tiers_json;

        return is_array($tiers) && ! empty($tiers)
            ? $tiers
            : \App\Services\Payroll\PeriodPaymentCalculationService::FALLBACK_TIERS;
    }

    public function getWeeksPerPeriod(): int
    {
        return \App\Services\Payroll\PeriodPaymentCalculationService::currentWeeksPerPeriod();
    }

    /** base_price ÷ weeks_per_period = one week-equivalent for period mode. */
    public function getPeriodUnitAmount(): float
    {
        $base  = (float) ($this->base_price ?? $this->getEffectivePaymentPerStudent() ?? 0);
        $weeks = $this->getWeeksPerPeriod();

        return $weeks > 0 ? round($base / $weeks, 2) : 0.0;
    }

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
