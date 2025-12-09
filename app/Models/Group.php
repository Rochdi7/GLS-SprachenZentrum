<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'site_id',
        'teacher_id',

        'name',
        'name_fr',
        'name_en',
        'name_ar',
        'name_de',

        'level',
        'period_label',
        'time_range',

        'description',
        'description_fr',
        'description_en',
        'description_ar',
        'description_de',

        'status',

        // NEW FIELDS – SUIVI DU GROUPE
        'date_debut',
        'date_fin',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Auto-detect period from time_range
     */
    public function setTimeRangeAttribute($value)
    {
        $this->attributes['time_range'] = $value;

        // Convert "10h00" to "10:00"
        $clean = preg_replace('/h/i', ':', $value);

        // Extract start time
        preg_match('/(\d{1,2}:\d{2})/', $clean, $match);

        if (!isset($match[1])) {
            $this->attributes['period_label'] = 'morning';
            return;
        }

        $start = $match[1];
        $hour = intval(explode(':', $start)[0]);

        // Determine period
        if ($hour >= 8 && $hour < 12) {
            $this->attributes['period_label'] = 'morning';
        } elseif ($hour >= 12 && $hour < 14) {
            $this->attributes['period_label'] = 'midday';
        } elseif ($hour >= 14 && $hour < 18) {
            $this->attributes['period_label'] = 'afternoon';
        } else {
            $this->attributes['period_label'] = 'evening';
        }
    }
}
