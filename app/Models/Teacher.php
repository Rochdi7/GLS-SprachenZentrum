<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Teacher extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'crm_teacher_id',
        'site_id',
        'name',
        'slug',
        'email',
        'phone',
        'speciality',
        'bio',
        'payment_per_student',
    ];

    protected $casts = [
        'payment_per_student' => 'decimal:2',
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

    /**
     * Additional centres a teacher is affected to. The "primary" one in
     * `site_id` is always included via accessibleSiteIds() — the pivot lists
     * every centre the teacher works in.
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_teacher')->withTimestamps();
    }

    /**
     * Union of the primary `site_id` and the multi-centre pivot, as ints.
     */
    public function accessibleSiteIds(): array
    {
        $ids = $this->sites()->pluck('sites.id')->all();
        if ($this->site_id) {
            $ids[] = $this->site_id;
        }
        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * The user account (professor login) linked to this teacher, if any.
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * All presence/payment imports belonging to this teacher's groups.
     */
    public function presenceImports()
    {
        return \App\Models\PresenceImport::whereIn(
            'group_id',
            $this->groups()->select('id')
        );
    }

    public function weeklyReports()
    {
        return $this->hasMany(WeeklyReport::class);
    }
}
