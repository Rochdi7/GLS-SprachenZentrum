<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSendLog extends Model
{
    protected $fillable = [
        'type', 'category', 'period_from', 'period_to',
        'recipients', 'status', 'error', 'sent_by',
    ];

    protected $casts = [
        'recipients'  => 'array',
        'period_from' => 'date',
        'period_to'   => 'date',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'weekly-presence'           => 'Présences',
            'weekly-prof-payment'       => 'Paiements profs',
            'weekly-unpaid-students'    => 'Étudiants impayés',
            'weekly-group-performance'  => 'Perf. groupes',
            'weekly-center-performance' => 'Perf. centres',
            'monthly-revenue'           => 'Revenus mensuel',
            'monthly-prof-payment'      => 'Paiements profs (mensuel)',
            default                     => $type,
        };
    }
}
