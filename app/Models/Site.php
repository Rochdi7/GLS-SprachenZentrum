<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Site extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'city',
        'address',
        'phone',
        'email',
        'video_title',
        'video_url',
        'video_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    // Auto slug + validation YouTube
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($site) {
            if (empty($site->slug)) {
                $site->slug = Str::slug($site->name);
            }
        });
    }

    // ✅ Validation YouTube manuelle si besoin
    public static function isYouTubeUrl($url)
    {
        return preg_match('/(youtube\.com|youtu\.be)/i', $url);
    }
}
