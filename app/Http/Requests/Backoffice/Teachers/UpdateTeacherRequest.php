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
            'site_id'     => 'required|exists:sites,id',
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
            'site_id.required' => 'Veuillez sélectionner un centre GLS.',
            'name.required'    => 'Le nom de l’enseignant est obligatoire.',
            'email.email'      => 'L’adresse email n’est pas valide.',
            'image.image'      => 'Le fichier doit être une image valide.',
            'image.max'        => 'L’image ne doit pas dépasser 4 MB.',
        ];
    }
}
