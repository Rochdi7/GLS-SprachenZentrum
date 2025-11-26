<?php

namespace App\Http\Requests\Backoffice\Groups;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // MUST BE TRUE
    }

    public function rules(): array
    {
        return [
            'site_id'       => 'required|exists:sites,id',
            'teacher_id'    => 'required|exists:teachers,id',

            'level'         => 'required|in:A1,A2,B1,B2',

            'period_label'  => 'required|string|max:255',
            'time_range'    => 'required|string|max:255',

            'description'   => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required'      => 'Veuillez sélectionner un centre GLS.',
            'teacher_id.required'   => 'Veuillez sélectionner un enseignant.',
            'level.required'        => 'Veuillez choisir un niveau.',
            'period_label.required' => 'Le nom de la période est obligatoire.',
            'time_range.required'   => 'L’horaire du groupe est obligatoire.',
        ];
    }
}
