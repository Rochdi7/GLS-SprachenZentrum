<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One student row from a presence import.
 *
 * Payment model: weekly flat-rate.
 * Each calendar week (1..4), if the student has >= weekly_threshold present days,
 * the prof earns base_price * weekly_rate_percent (default 25%) for that student-week.
 * A human can override the per-week amount via week_N_amount_override.
 */
class PresenceImportStudent extends Model
{
    public const WEEKS = [1, 2, 3, 4];

    protected $fillable = [
        'presence_import_id',
        'row_number',
        'student_name',
        'total_present',
        'total_absent',
        'week_1_presence',
        'week_2_presence',
        'week_3_presence',
        'week_4_presence',
        'week_1_amount',
        'week_2_amount',
        'week_3_amount',
        'week_4_amount',
        'week_1_amount_override',
        'week_2_amount_override',
        'week_3_amount_override',
        'week_4_amount_override',
        'active_quarters',
        'category',
        'category_override',
        'weighted_amount',
        'status',
        'row_color',
        'raw_data',
        'crm_student_id',
    ];

    protected $casts = [
        'weighted_amount' => 'decimal:2',
        'week_1_amount' => 'decimal:2',
        'week_2_amount' => 'decimal:2',
        'week_3_amount' => 'decimal:2',
        'week_4_amount' => 'decimal:2',
        'week_1_amount_override' => 'decimal:2',
        'week_2_amount_override' => 'decimal:2',
        'week_3_amount_override' => 'decimal:2',
        'week_4_amount_override' => 'decimal:2',
        'raw_data' => 'array',
    ];

    /* ------------------------------------------------------------------ */
    /*  Legacy category constants (kept for backwards compatibility)        */
    /* ------------------------------------------------------------------ */

    public const CATEGORY_FULL = 'full';

    public const CATEGORY_THREE_QUARTER = 'three_quarter';

    public const CATEGORY_HALF = 'half';

    public const CATEGORY_QUARTER = 'quarter';

    public const CATEGORY_ZERO = 'zero';

    public const CATEGORY_FRACTIONS = [
        self::CATEGORY_FULL => 1.00,
        self::CATEGORY_THREE_QUARTER => 0.75,
        self::CATEGORY_HALF => 0.50,
        self::CATEGORY_QUARTER => 0.25,
        self::CATEGORY_ZERO => 0.00,
    ];

    public const CATEGORY_LABELS = [
        self::CATEGORY_FULL => 'Complet',
        self::CATEGORY_THREE_QUARTER => '3/4',
        self::CATEGORY_HALF => '1/2',
        self::CATEGORY_QUARTER => '1/4',
        self::CATEGORY_ZERO => 'Zéro',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function presenceImport()
    {
        return $this->belongsTo(PresenceImport::class);
    }

    public function records()
    {
        return $this->hasMany(PresenceRecord::class)->orderBy('date');
    }

    /* ------------------------------------------------------------------ */
    /*  Weekly helpers                                                     */
    /* ------------------------------------------------------------------ */

    public function getWeekPresence(int $week): int
    {
        return (int) ($this->{"week_{$week}_presence"} ?? 0);
    }

    public function getWeekAutoAmount(int $week): float
    {
        return (float) ($this->{"week_{$week}_amount"} ?? 0);
    }

    public function getWeekOverride(int $week): ?float
    {
        $v = $this->{"week_{$week}_amount_override"};

        return $v === null ? null : (float) $v;
    }

    public function getWeekEffectiveAmount(int $week): float
    {
        $override = $this->getWeekOverride($week);

        return $override !== null ? $override : $this->getWeekAutoAmount($week);
    }

    public function isWeekOverridden(int $week): bool
    {
        return $this->getWeekOverride($week) !== null;
    }

    public function getTotalAmount(): float
    {
        $total = 0.0;
        foreach (self::WEEKS as $w) {
            $total += $this->getWeekEffectiveAmount($w);
        }

        return $total;
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }
}
