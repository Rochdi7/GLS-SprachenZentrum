<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BlogPost extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'category_id',

        'title_fr',
        'title_en',

        'slug',

        'content_fr',
        'content_en',

        'reading_time',
        'featured',
        'status',
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title_fr . '-' . uniqid());
            }
        });
    }

    /**
     * MEDIA LIBRARY
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('blog_images')->singleFile();
    }

    /**
     * 🔥 Accessor multilangue pour le titre
     */
    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        $field = "title_{$locale}";

        return $this->$field ?: $this->title_fr;
    }

    /**
     * 🔥 Accessor multilangue pour le contenu
     */
    public function getContentAttribute()
    {
        $locale = app()->getLocale();
        $field = "content_{$locale}";

        return $this->$field ?: $this->content_fr;
    }
}
