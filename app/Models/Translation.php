<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Translation extends Model
{
    protected $fillable = [
        'cin',
        'student_name',
        'phone',
        'date_received',
        'date_handed_over',
        'status',
        'notes',
        'total_cost',
    ];

    protected $casts = [
        'date_received'    => 'date',
        'date_handed_over' => 'date',
        'total_cost'       => 'integer',
    ];

    public const STATUS_PENDING    = 'pending';
    public const STATUS_TRANSLATOR = 'translator';
    public const STATUS_DELIVERED  = 'delivered';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING    => 'Reçu (GLS)',
            self::STATUS_TRANSLATOR => 'Chez Traducteur',
            self::STATUS_DELIVERED  => "Rendu à l'étudiant",
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public static function normalizeCin(?string $cin): string
    {
        return strtoupper(trim((string) $cin));
    }

    public function items(): HasMany
    {
        return $this->hasMany(TranslationItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->total_cost = (int) $this->items()->sum('line_total');
        $this->saveQuietly();
    }

    public function totalPages(): int
    {
        return (int) $this->items->sum('page_count');
    }
}
