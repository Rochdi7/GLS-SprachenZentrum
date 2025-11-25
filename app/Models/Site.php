<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Site extends Model
{
    protected $fillable = [
        'name', 'slug', 'city', 'address',
        'phone', 'email',
        'subtitle', 'hero_image',
        'about_title', 'about_subtitle', 'about_content',
        'offer_title', 'offer_subtitle', 'offer_content',
        'video_title', 'video_description', 'video_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    // Auto slug + YouTube validation
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($site) {
            if (empty($site->slug)) {
                $site->slug = Str::slug($site->name);
            }

            if (!empty($site->video_url) && !self::isYouTubeUrl($site->video_url)) {
                throw new \Exception("Video URL must be a YouTube link (youtube.com or youtu.be)");
            }
        });
    }

    public static function isYouTubeUrl($url)
    {
        return preg_match('/(youtube\.com|youtu\.be)/i', $url);
    }
}
