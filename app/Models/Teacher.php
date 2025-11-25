<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Teacher extends Model
{
    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'email',
        'phone',
        'speciality',
        'bio',
        'image',   // added image, nullable
    ];

    // Relationships
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    // Auto slug
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($teacher) {
            if (empty($teacher->slug)) {
                $teacher->slug = Str::slug($teacher->name);
            }
        });
    }
}
