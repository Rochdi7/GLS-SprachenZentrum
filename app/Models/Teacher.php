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

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function weeklyReports()
    {
        return $this->hasMany(WeeklyReport::class);
    }
}
