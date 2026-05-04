<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationItem extends Model
{
    protected $fillable = [
        'translation_id',
        'doc_type',
        'page_count',
        'price_per_page',
        'line_total',
    ];

    protected $casts = [
        'page_count'     => 'integer',
        'price_per_page' => 'integer',
        'line_total'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (TranslationItem $item) {
            $item->line_total = (int) $item->page_count * (int) $item->price_per_page;
        });

        static::saved(function (TranslationItem $item) {
            $item->translation?->recalculateTotal();
        });

        static::deleted(function (TranslationItem $item) {
            $item->translation?->recalculateTotal();
        });
    }

    public function translation(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }
}
