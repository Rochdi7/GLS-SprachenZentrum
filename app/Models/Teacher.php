<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Teacher extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'email',
        'phone',
        'speciality',
        'bio',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($teacher) {

            if (empty($teacher->slug)) {
                $teacher->slug = Str::slug($teacher->name);
            }
        });
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
