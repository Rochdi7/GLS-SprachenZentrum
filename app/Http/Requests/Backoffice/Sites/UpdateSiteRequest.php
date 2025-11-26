<?php

namespace App\Http\Requests\Backoffice\Sites;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // IMPORTANT
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'city'              => 'required|string|max:255',
            'address'           => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:255',
            'subtitle'          => 'nullable|string|max:255',

            // About
            'about_title'       => 'nullable|string',
            'about_subtitle'    => 'nullable|string',
            'about_content'     => 'nullable|string',

            // Offer
            'offer_title'       => 'nullable|string',
            'offer_subtitle'    => 'nullable|string',
            'offer_content'     => 'nullable|string',

            // Video
            'video_title'       => 'nullable|string|max:255',
            'video_description' => 'nullable|string',
            'video_url'         => 'nullable|url|regex:/(youtube\.com|youtu\.be)/i',

            // Hero image
            'hero_image'        => 'nullable|image|max:4096',
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
            'hero_image.image'      => 'L’image du hero doit être un fichier image.',
            'hero_image.max'        => 'L’image ne doit pas dépasser 4MB.',
        ];
    }
}
