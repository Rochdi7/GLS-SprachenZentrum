<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'site_id',
        'teacher_id',
        'level',
        'period_label',
        'time_range',
        'description',
    ];

    // Relationships
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
