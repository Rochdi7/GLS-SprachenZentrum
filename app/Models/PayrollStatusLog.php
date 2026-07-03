<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One immutable audit entry for a payroll lifecycle action.
 */
class PayrollStatusLog extends Model
{
    protected $fillable = [
        'presence_import_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'comment',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /* Action constants */
    public const ACTION_VALIDATE         = 'validate';
    public const ACTION_RETURN_TO_DRAFT  = 'return_to_draft';
    public const ACTION_MARK_PAID        = 'mark_paid';
    public const ACTION_LOCK             = 'lock';
    public const ACTION_UNLOCK           = 'unlock';
    public const ACTION_RECALCULATE      = 'recalculate';
    public const ACTION_OVERRIDE         = 'override';

    /** Human labels for display. */
    public const ACTION_LABELS = [
        self::ACTION_VALIDATE        => 'Validé',
        self::ACTION_RETURN_TO_DRAFT => 'Retour au brouillon',
        self::ACTION_MARK_PAID       => 'Marqué payé',
        self::ACTION_LOCK            => 'Verrouillé',
        self::ACTION_UNLOCK          => 'Déverrouillé',
        self::ACTION_RECALCULATE     => 'Recalculé',
        self::ACTION_OVERRIDE        => 'Ajustement manuel',
    ];

    public function presenceImport()
    {
        return $this->belongsTo(PresenceImport::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }
}
