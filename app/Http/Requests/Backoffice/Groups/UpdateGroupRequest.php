<?php

namespace App\Http\Requests\Backoffice\Groups;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id'       => 'required|exists:sites,id',
            'teacher_id'    => 'required|exists:teachers,id',

            'level'         => 'required|in:A1,A2,B1,B2',

            'name'          => 'required|string|max:255',
            'name_fr'       => 'nullable|string|max:255',
            'name_en'       => 'nullable|string|max:255',

            // Removed: period_label (auto generated)
            'time_range'    => 'required|string|max:255',

            'status'        => 'required|in:active,upcoming',

            /*******************************************
             * SUIVI DU GROUPE
             *******************************************/
            'date_debut'    => 'required|date',
            'date_fin'      => 'required|date|after_or_equal:date_debut',
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required'      => 'Veuillez sélectionner un centre GLS.',
            'teacher_id.required'   => 'Veuillez sélectionner un enseignant.',
            'level.required'        => 'Veuillez choisir un niveau.',

            'name.required'         => 'Le nom du groupe est obligatoire.',
            'time_range.required'   => 'L’horaire du groupe est obligatoire.',

            // Suivi du groupe
            'date_debut.required'   => 'La date de début est obligatoire.',
            'date_fin.required'     => 'La date de fin est obligatoire.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}
