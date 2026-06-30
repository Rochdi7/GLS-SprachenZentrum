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
        'email',
        'date_received',
        'date_handed_over',
        'ready_notified_at',
        'status',
        'notes',
        'total_cost',
    ];

    protected $casts = [
        'date_received'     => 'date',
        'date_handed_over'  => 'date',
        'ready_notified_at' => 'datetime',
        'total_cost'        => 'integer',
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

    /**
     * Phone in international digits-only form for wa.me links.
     * Assumes Moroccan numbers (06… / 07…) → 2126… / 2127… when no country code.
     */
    public function whatsappNumber(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '212')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '212' . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Ready-to-send French WhatsApp message announcing the documents are ready.
     */
    public function whatsappReadyMessage(): string
    {
        $name = trim($this->student_name) ?: 'cher étudiant';

        return "Bonjour {$name},\n\n"
            . "Bonne nouvelle ! La traduction de vos documents est terminée et vos papiers sont prêts à être récupérés chez GLS Sprachenzentrum.\n\n"
            . "N'hésitez pas à passer à nos bureaux pour les retirer.\n\n"
            . 'Cordialement,
L\'équipe GLS Sprachenzentrum';
    }

    public function whatsappReadyUrl(): ?string
    {
        $number = $this->whatsappNumber();
        if (! $number) {
            return null;
        }

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($this->whatsappReadyMessage());
    }
}
