<?php

namespace App\Http\Requests\Backoffice\Studienkolleg;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudienkollegRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],

            'hero_image' => ['nullable', 'image', 'max:4096'],
            'card_image' => ['nullable', 'image', 'max:4096'],
            'university_logo' => ['nullable', 'image', 'max:2048'],
            'video_url' => ['nullable', 'string'],

            'featured' => ['sometimes', 'boolean'],
            'public' => ['sometimes', 'boolean'],
            'uni_assist' => ['sometimes', 'boolean'],
            'entrance_exam' => ['sometimes', 'boolean'],

            'duration_semesters' => ['sometimes', 'integer', 'min:1'],
            'tuition' => ['sometimes', 'string', 'max:50'],
            'language_of_instruction' => ['sometimes', 'string', 'max:50'],

            'courses' => ['nullable', 'array'],
            'courses.*' => ['string', 'max:50'],

            'languages' => ['nullable', 'string'],
            'documents' => ['nullable', 'string'],

            'deadlines' => ['nullable', 'array'],

            'application_url' => ['nullable', 'url'],
            'exam_url' => ['nullable', 'url'],
            'official_website' => ['nullable', 'url'],
            'contact_email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ];
    }
}
