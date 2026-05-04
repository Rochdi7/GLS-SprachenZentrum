<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'cin',
        'student_name',
        'phone',
        'doc_type',
        'page_count',
        'price_per_page',
        'total_cost',
        'date_received',
        'date_handed_over',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_received'    => 'date',
        'date_handed_over' => 'date',
        'page_count'       => 'integer',
        'price_per_page'   => 'integer',
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
}
