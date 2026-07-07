<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attestation extends Model
{
    protected $fillable = [
        'last_name',
        'first_name',
        'birth_date',
        'birth_place',

        'group_id',
        'site_id',
        'is_legacy',
        'level',
        'level_from',

        'course_start_date',
        'course_end_date',

        'niveau_start_date',
        'niveau_end_date',
        'is_ongoing',

        'units_45min',
        'hours_per_session',

        'fees_status',

        'stufe_index',
        'stufe_total',
        'erfolg',
        'methodology_text',
        'language',

        'city',
        'issue_date',

        'attestation_number',
        'public_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'course_start_date' => 'date',
        'course_end_date' => 'date',
        'niveau_start_date' => 'date',
        'niveau_end_date' => 'date',
        'issue_date' => 'date',
        'units_45min' => 'integer',
        'hours_per_session' => 'decimal:2',
        'stufe_index' => 'integer',
        'stufe_total' => 'integer',
        'is_ongoing' => 'boolean',
        'is_legacy' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function getFullNameAttribute(): string
    {
        return strtoupper($this->last_name).' '.strtoupper($this->first_name);
    }

    /**
     * Levels to check on the PDF. When `level_from` is set, returns every level
     * between `level_from` and `level` inclusive (e.g. A1 → B1 = [A1, A2, B1]).
     * Otherwise, just the single `level`.
     */
    public function getCheckedLevelsAttribute(): array
    {
        $order = ['A1', 'A2', 'B1', 'B2', 'C1'];

        if (empty($this->level_from) || $this->level_from === $this->level) {
            return array_filter([$this->level]);
        }

        $fromIdx = array_search($this->level_from, $order, true);
        $toIdx = array_search($this->level, $order, true);

        if ($fromIdx === false || $toIdx === false || $fromIdx > $toIdx) {
            return array_filter([$this->level]);
        }

        return array_slice($order, $fromIdx, $toIdx - $fromIdx + 1);
    }

    protected static function booted(): void
    {
        static::creating(function (Attestation $att) {
            if (empty($att->public_token)) {
                $att->public_token = Str::random(48);
            }

            if (empty($att->attestation_number)) {
                $att->attestation_number = self::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'ATT-'.now()->format('Ym');
        $last = static::where('attestation_number', 'like', $prefix.'%')
            ->orderByDesc('attestation_number')
            ->value('attestation_number');

        $next = 1;
        if ($last) {
            $lastNum = (int) substr($last, strrpos($last, '-') + 1);
            $next = $lastNum + 1;
        }

        return $prefix.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
