<?php

namespace App\Http\Requests\Backoffice\Sites;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Base
            'name'              => 'required|string|max:255',
            'city'              => 'required|string|max:255',
            'address'           => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:255',

            // Bloc vidéo 9onsol Talks ✅ (update aussi)
            'video_title'       => 'nullable|string|max:255',
            'video_description' => 'nullable|string',
            'video_url'         => 'nullable|url|regex:/(youtube\.com|youtu\.be)/i',

            // Status
            'is_active'         => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Le nom du site est obligatoire.',
            'city.required'         => 'La ville est obligatoire.',
            'email.email'           => 'Adresse email invalide.',
            'video_url.url'         => 'Le lien vidéo doit être une URL valide.',
            'video_url.regex'       => 'La vidéo doit provenir de YouTube.',
        ];
    }
}
