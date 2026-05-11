<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Builder;

class Studienkolleg extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'slug', 'university', 'city', 'state', 'country', 'hero_image', 'card_image', 'university_logo', 'video_url', 'featured', 'public', 'uni_assist', 'entrance_exam', 'duration_semesters', 'tuition', 'language_of_instruction', 'courses', 'languages', 'documents', 'deadlines', 'requirements', 'application_method', 'application_portal_note', 'application_url', 'exam_subjects', 'exam_link', 'exam_url', 'certification_required', 'translation_required', 'translation_note', 'official_website', 'contact_email', 'address', 'map_embed', 'meta_title', 'meta_description'];

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

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        // Search global
        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('university', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('state', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%");
            });
        }

        // City
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Language (ton input s'appelle "lang", colonne = language_of_instruction)
        if (!empty($filters['lang'])) {
            $query->where('language_of_instruction', $filters['lang']);
        }

        // Course (si "courses" est cast en array/json) — matches "T" or "T Course" stored values
        if (!empty($filters['course'])) {
            $code = strtoupper(trim($filters['course']));
            $query->where(function ($sub) use ($code) {
                $sub->whereJsonContains('courses', $code)
                    ->orWhereJsonContains('courses', $code . ' Course');
            });
        }

        // German level (B1 / B2) — stored as text inside requirements JSON
        if (!empty($filters['german_level'])) {
            $level = strtoupper(trim($filters['german_level']));
            $query->where('requirements', 'like', '%German level: ' . $level . '%');
        }

        // Booleans (checkbox/select)
        foreach (['uni_assist', 'entrance_exam', 'certification_required', 'translation_required'] as $key) {
            if (array_key_exists($key, $filters)) {
                $query->where($key, (bool) $filters[$key]);
            }
        }

        // Featured flag (si tu l’utilises comme filtre)
        if (array_key_exists('featured', $filters)) {
            $query->where('featured', (bool) $filters['featured']);
        }

        return $query;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('studienkolleg_hero')->singleFile();
        $this->addMediaCollection('studienkolleg_card')->singleFile();
        $this->addMediaCollection('university_logo')->singleFile();
    }
}
