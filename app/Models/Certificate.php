<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

        // Written
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

        // Oral
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
    ];

    protected $casts = [
        'birth_date'     => 'date',
        'exam_date'      => 'date',
        'issue_date'     => 'date',

        'written_total'        => 'integer',
        'written_max'          => 'integer',

        'reading_score'        => 'integer',
        'reading_max'          => 'integer',

        'grammar_score'        => 'integer',
        'grammar_max'          => 'integer',

        'listening_score'      => 'integer',
        'listening_max'        => 'integer',

        'writing_score'        => 'integer',
        'writing_max'          => 'integer',

        'oral_total'           => 'integer',
        'oral_max'             => 'integer',

        'presentation_score'   => 'integer',
        'presentation_max'     => 'integer',

        'discussion_score'     => 'integer',
        'discussion_max'       => 'integer',

        'problemsolving_score' => 'integer',
        'problemsolving_max'   => 'integer',
    ];



    /*
    |--------------------------------------------------------------------------
    |  ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getTotalMaxAttribute()
    {
        return $this->written_max + $this->oral_max;
    }

    public function getTotalScoreAttribute()
    {
        return $this->written_total + $this->oral_total;
    }

    public function getTotalPercentageAttribute()
    {
        return $this->total_max == 0
            ? 0
            : round(($this->total_score / $this->total_max) * 100, 2);
    }

    public function getWrittenPercentageAttribute()
    {
        return $this->written_max == 0
            ? 0
            : round(($this->written_total / $this->written_max) * 100, 2);
    }

    public function getOralPercentageAttribute()
    {
        return $this->oral_max == 0
            ? 0
            : round(($this->oral_total / $this->oral_max) * 100, 2);
    }

    public function getFullNameAttribute()
    {
        return strtoupper($this->last_name) . ' ' . ucfirst($this->first_name);
    }
}
