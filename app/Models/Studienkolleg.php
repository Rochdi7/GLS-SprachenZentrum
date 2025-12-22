<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Studienkolleg extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'university',
        'city',
        'state',
        'country',

        'hero_image',
        'card_image',
        'university_logo',
        'video_url',

        'featured',
        'public',
        'uni_assist',
        'entrance_exam',

        'duration_semesters',
        'tuition',
        'language_of_instruction',

        'courses',
        'languages',
        'documents',
        'deadlines',
        'requirements',

        'application_method',
        'application_portal_note',
        'application_url',

        'exam_subjects',
        'exam_link',

        'certification_required',
        'translation_required',
        'translation_note',

        'official_website',
        'contact_email',
        'address',
        'map_embed',

        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'public' => 'boolean',
        'uni_assist' => 'boolean',
        'entrance_exam' => 'boolean',

        'certification_required' => 'boolean',
        'translation_required' => 'boolean',

        'courses' => 'array',
        'languages' => 'array',
        'documents' => 'array',
        'deadlines' => 'array',
        'requirements' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('studienkolleg_hero')->singleFile();
        $this->addMediaCollection('studienkolleg_card')->singleFile();
        $this->addMediaCollection('university_logo')->singleFile();
    }
}
