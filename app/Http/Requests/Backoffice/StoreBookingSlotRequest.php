<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'centre_id' => ['required', 'exists:sites,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'centre_id.required' => 'Veuillez sélectionner un centre GLS.',
            'centre_id.exists' => 'Le centre sélectionné est invalide.',

            'date.required' => 'La date est obligatoire.',
            'date.date' => 'La date doit être valide.',

            'time.required' => "L'horaire est obligatoire.",
            'time.date_format' => "L'horaire doit être au format HH:MM.",

            'is_available.boolean' => "Le champ disponibilité doit être vrai ou faux.",
        ];
    }
}
