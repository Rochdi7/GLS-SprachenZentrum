<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SiteExpense extends Model
{
    public const TYPES = [
        'paiement_prof' => 'Paiement prof',
        'impots_taxes' => 'Impôts et taxes',
        'eau_electricite' => 'Eau et Electricité',
        'loyer' => 'Loyer',
        'internet' => 'Internet',
        'fournitures' => 'Fournitures',
        'produits_consommables' => 'Produits consommables',
        'logistiques' => 'Logistiques',
        'externalisation' => 'Externalisation / Sous-traitance',
        'salaire' => 'Salaire',
        'autre' => 'Autre',
    ];

    /**
     * Map from PDF "Type" text to our normalised type key.
     */
    public const TYPE_MAP = [
        'paiement prof' => 'paiement_prof',
        'impôts et taxes' => 'impots_taxes',
        'impots et taxes' => 'impots_taxes',
        'eau et electricité' => 'eau_electricite',
        'eau et electricite' => 'eau_electricite',
        'produits consommables' => 'produits_consommables',
        'logistiques' => 'logistiques',
        'externalisation ou sous-traitance' => 'externalisation',
        'externalisation' => 'externalisation',
        'loyer' => 'loyer',
        'internet' => 'internet',
        'fournitures' => 'fournitures',
        'salaire' => 'salaire',
    ];

    protected $fillable = [
        'site_id', 'type', 'label', 'amount', 'month', 'notes',
        'reference', 'expense_date', 'payment_method', 'operator_name',
        'order_number', 'expense_import_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'month' => 'date',
        'expense_date' => 'date',
    ];

    /**
     * Normalise a type label from PDF to our key.
     */
    public static function normalizeType(string $raw): string
    {
        $lower = mb_strtolower(trim($raw));
        return self::TYPE_MAP[$lower] ?? 'autre';
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeForSite(Builder $query, int $siteId): Builder
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForMonth(Builder $query, string $month): Builder
    {
        return $query->where('month', $month);
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
