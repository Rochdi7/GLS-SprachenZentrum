<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'last_name', 'first_name', 'birth_date', 'birth_place',
        'exam_level', 'exam_date', 'issue_date', 'certificate_number',
        'written_total', 'written_max',
        'reading_score', 'reading_max',
        'grammar_score', 'grammar_max',
        'listening_score', 'listening_max',
        'writing_score', 'writing_max',
        'oral_total', 'oral_max',
        'presentation_score', 'presentation_max',
        'discussion_score', 'discussion_max',
        'problemsolving_score', 'problemsolving_max',
        'final_result',
    ];

    protected $casts = [
        'birth_date'   => 'date',
        'exam_date'    => 'date',
        'issue_date'   => 'date',
    ];

    // Full name accessor
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
