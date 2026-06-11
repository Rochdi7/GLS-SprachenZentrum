<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Spatie Media Library
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// Spatie Permission
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, HasRoles;

    public const STAFF_ROLES = [
        'Administration',
        'Réception',
        'Commercial',
        'Manager',
        'Coordination',
        'Caissier',
        'Autre',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'location',
        'bio',
        'site_id',
        'staff_role',
        'hired_at',
        'is_active',
        'staff_notes',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'hired_at'          => 'date',
        'is_active'         => 'boolean',
        'last_login_at'     => 'datetime',
    ];

    // ── Staff relations ──────────────────────────────────────────

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Additional centres a user can access. The "primary" one in `site_id` is
     * always included via accessibleSiteIds() — the pivot lists every centre
     * the user can see/manage.
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_user')->withTimestamps();
    }

    /**
     * IDs of every centre this user is affected to (primary + pivot), de-duped.
     */
    public function accessibleSiteIds(): array
    {
        $ids = $this->sites()->pluck('sites.id')->all();
        if ($this->site_id) {
            $ids[] = $this->site_id;
        }
        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(UserSchedule::class);
    }

    public function encaissements(): HasMany
    {
        return $this->hasMany(Encaissement::class);
    }

    public function primes(): HasMany
    {
        return $this->hasMany(Prime::class);
    }

    // ── Media ────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(80)
            ->height(80)
            ->nonQueued();
    }

    public function avatarUrl(): string
    {
        $media = $this->getFirstMedia('profile_photo');
        return $media
            ? $media->getUrl('thumb')
            : asset('assets/images/user/avatar-2.avif');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isCenterAdmin(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Admin', 'Manager']);
    }

    public function canManageSite(?int $siteId): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }
        if ($siteId === null || ! $this->isCenterAdmin()) {
            return false;
        }
        return in_array((int) $siteId, $this->accessibleSiteIds(), true);
    }

    /**
     * Read-only: true if this user is affected to the given centre (primary or pivot).
     */
    public function canAccessSite(?int $siteId): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }
        if ($siteId === null) {
            return false;
        }
        return in_array((int) $siteId, $this->accessibleSiteIds(), true);
    }
}
