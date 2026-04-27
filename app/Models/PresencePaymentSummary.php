<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Final payment calculation summary for a presence import.
 * One row per import = one month for one group.
 */
class PresencePaymentSummary extends Model
{
    protected $fillable = [
        'presence_import_id',
        'base_price',
        'count_full',
        'count_three_quarter',
        'count_half',
        'count_quarter',
        'count_zero',
        'count_qualified_weeks',
        'count_unqualified_weeks',
        'weekly_unit_amount',
        'total_students',
        'total_payment',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'weekly_unit_amount' => 'decimal:2',
        'total_payment' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function presenceImport()
    {
        return $this->belongsTo(PresenceImport::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }
}
