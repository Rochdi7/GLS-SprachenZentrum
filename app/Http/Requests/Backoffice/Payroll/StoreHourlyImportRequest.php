<?php

namespace App\Http\Requests\Backoffice\Payroll;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creation of an HOURLY-mode professor payment import.
 * Group is optional in hourly mode (professor paid for hours, not per class),
 * but a CRM class is still used to attach the professor identity.
 */
class StoreHourlyImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'crm_class_id'       => ['required', 'integer', 'min:1'],
            'attached_month'     => ['required', 'integer', 'between:1,12'],
            'attached_year'      => ['required', 'integer', 'between:2000,2100'],
            'group_month_number' => ['nullable', 'integer', 'between:1,60'],
            'hourly_rate'        => ['required', 'numeric', 'min:0', 'max:9999999'],
            'total_hours'        => ['required', 'numeric', 'min:0', 'max:100000'],
            'month_label'        => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'crm_class_id.required'   => 'Veuillez sélectionner une classe / un professeur.',
            'attached_month.required' => 'Veuillez indiquer le mois de rattachement.',
            'attached_month.between'  => 'Le mois de rattachement doit être compris entre 1 et 12.',
            'attached_year.required'  => 'Veuillez indiquer l’année de rattachement.',
            'hourly_rate.required'    => 'Veuillez indiquer le taux horaire.',
            'hourly_rate.numeric'     => 'Le taux horaire doit être un nombre.',
            'total_hours.required'    => 'Veuillez indiquer le nombre total d’heures.',
            'total_hours.numeric'     => 'Le nombre d’heures doit être un nombre.',
        ];
    }
}
