<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Site extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

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
        'crm_store_id',
        'crm_token',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'crm_store_id' => 'integer',
    ];

    protected $hidden = [
        'crm_token',
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

    public function staff()
    {
        return $this->hasMany(User::class)->whereNotNull('staff_role');
    }

    /**
     * All users (any role) affected to this centre via the multi-site pivot.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'site_user')->withTimestamps();
    }

    // BC alias
    public function employees()
    {
        return $this->staff();
    }

    public function encaissements()
    {
        return $this->hasMany(Encaissement::class);
    }

    public function encaissementImports()
    {
        return $this->hasMany(EncaissementImport::class);
    }

    public function expenses()
    {
        return $this->hasMany(SiteExpense::class);
    }

    public function primes()
    {
        return $this->hasMany(Prime::class);
    }

    public function impayes()
    {
        return $this->hasMany(Impaye::class);
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

    /**
     * Get course duration (in hours) based on center
     * 2.5h for: Marrakech, Agadir
     * 2h for everyone else: Rabat, Casablanca, Kenitra, Sale, Online
     */
    public function getCourseDuration()
    {
        $twoHalfCenters = ['marrakech', 'agadir'];
        if (in_array(strtolower($this->slug), $twoHalfCenters)) {
            return 2.5;
        }
        // Default to 2h for all other centers (Rabat, Casablanca, Kenitra, Sale, Online)
        return 2;
    }
}
