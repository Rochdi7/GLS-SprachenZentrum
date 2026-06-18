<?php

namespace App\Http\Requests\Backoffice\Teachers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // VERY IMPORTANT
    }

    public function rules(): array
    {
        return [
            'site_ids'    => 'required|array|min:1',
            'site_ids.*'  => 'integer|exists:sites,id',
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'speciality'  => 'nullable|string|max:255',
            'bio'         => 'nullable|string',

            'image'       => 'nullable|image|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'site_ids.required' => 'Veuillez sélectionner au moins un centre GLS.',
            'site_ids.min'      => 'Veuillez sélectionner au moins un centre GLS.',
            'name.required'    => 'Le nom de l’enseignant est obligatoire.',
            'email.email'      => 'L’adresse email n’est pas valide.',
            'image.image'      => 'Le fichier doit être une image valide.',
            'image.max'        => 'L’image ne doit pas dépasser 4 MB.',
        ];
    }
}
