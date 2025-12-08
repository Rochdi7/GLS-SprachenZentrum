<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    protected $fillable = [
        'name_fr',
        'name_en',
        'slug',
        'is_active',
    ];

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name_fr);
            }
        });
    }

    /**
     * 🔥 Multilingual name helper
     */
    public function getName()
{
    $locale = app()->getLocale();
    $field = "name_{$locale}";

    return $this->$field ?: $this->name_fr;
}public function getNameAttribute()
{
    $locale = app()->getLocale();
    $field = "name_{$locale}";

    return $this->$field ?: $this->name_fr;
}

}
