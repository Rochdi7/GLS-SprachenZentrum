<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = [
        // Personal Info
        'last_name',
        'first_name',
        'birth_date',
        'birth_place',

        // Exam Meta
        'exam_level',
        'exam_date',
        'issue_date',
        'certificate_number',
        'certificate_type', // 'a2' or 'b2'
        'site_id',

        // Written (B2) / Lesen+Hören (A2)
        'written_total',
        'written_max',

        'reading_score',
        'reading_max',

        'grammar_score',
        'grammar_max',

        'listening_score',
        'listening_max',

        'writing_score',
        'writing_max',

        // Speaking (A2: Sprechen)
        'speaking_score',
        'speaking_max',

        // Oral (B2 only)
        'oral_total',
        'oral_max',

        'presentation_score',
        'presentation_max',

        'discussion_score',
        'discussion_max',

        'problemsolving_score',
        'problemsolving_max',

        // Final
        'final_result',
        'ergebnis_note',

        'public_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'exam_date' => 'date',
        'issue_date' => 'date',

        'written_total' => 'integer',
        'written_max' => 'integer',

        'reading_score' => 'integer',
        'reading_max' => 'integer',

        'grammar_score' => 'integer',
        'grammar_max' => 'integer',

        'listening_score' => 'integer',
        'listening_max' => 'integer',

        'writing_score' => 'integer',
        'writing_max' => 'integer',

        'speaking_score' => 'integer',
        'speaking_max' => 'integer',

        'oral_total' => 'integer',
        'oral_max' => 'integer',

        'presentation_score' => 'integer',
        'presentation_max' => 'integer',

        'discussion_score' => 'integer',
        'discussion_max' => 'integer',

        'problemsolving_score' => 'integer',
        'problemsolving_max' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    |  HELPERS
    |--------------------------------------------------------------------------
    */

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function isA2(): bool
    {
        return $this->certificate_type === 'a2';
    }

    public function isB2(): bool
    {
        return $this->certificate_type === 'b2';
    }

    /*
    |--------------------------------------------------------------------------
    |  ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getTotalMaxAttribute()
    {
        if ($this->isA2()) {
            return ($this->reading_max ?? 0) + ($this->listening_max ?? 0)
                 + ($this->writing_max ?? 0) + ($this->speaking_max ?? 0);
        }

        return ($this->written_max ?? 0) + ($this->oral_max ?? 0);
    }

    public function getTotalScoreAttribute()
    {
        if ($this->isA2()) {
            return ($this->reading_score ?? 0) + ($this->listening_score ?? 0)
                 + ($this->writing_score ?? 0) + ($this->speaking_score ?? 0);
        }

        return ($this->written_total ?? 0) + ($this->oral_total ?? 0);
    }

    public function getTotalPercentageAttribute()
    {
        return $this->total_max == 0 ? 0 : round(($this->total_score / $this->total_max) * 100, 2);
    }

    public function getWrittenPercentageAttribute()
    {
        return $this->written_max == 0 ? 0 : round((($this->written_total ?? 0) / $this->written_max) * 100, 2);
    }

    public function getOralPercentageAttribute()
    {
        return $this->oral_max == 0 ? 0 : round((($this->oral_total ?? 0) / $this->oral_max) * 100, 2);
    }

    public function getFullNameAttribute()
    {
        return strtoupper($this->last_name) . ' ' . ucfirst($this->first_name);
    }

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate) {
            if (empty($certificate->public_token)) {
                $certificate->public_token = Str::random(48);
            }

            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
        });
    }

    public static function generateCertificateNumber(): string
    {
        $prefix = 'GLS-' . now()->format('Ym');
        $last = static::where('certificate_number', 'like', $prefix . '%')
            ->orderByDesc('certificate_number')
            ->value('certificate_number');

        $next = 1;
        if ($last) {
            $lastNum = (int) substr($last, strrpos($last, '-') + 1);
            $next = $lastNum + 1;
        }

        return $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
