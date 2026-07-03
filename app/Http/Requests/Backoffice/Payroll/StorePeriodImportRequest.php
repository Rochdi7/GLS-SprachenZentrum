<?php

namespace App\Http\Requests\Backoffice\Payroll;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creation of a PERIOD-mode professor payment import.
 * Produces friendly French messages — never raw SQL / DB errors.
 */
class StorePeriodImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'crm_class_id'       => ['required', 'integer', 'min:1'],
            'date_start'         => ['required', 'date'],
            'date_end'           => ['required', 'date', 'after_or_equal:date_start'],
            'attached_month'     => ['required', 'integer', 'between:1,12'],
            'attached_year'      => ['required', 'integer', 'between:2000,2100'],
            'group_month_number' => ['required', 'integer', 'between:1,60'],
            'base_price'         => ['required', 'numeric', 'min:0', 'max:9999999'],
            'month_label'        => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Guard the CRM date-range ceiling with a clean message rather than a
        // downstream runtime exception.
        if ($this->filled('date_start') && $this->filled('date_end')) {
            try {
                $start = \Carbon\Carbon::parse($this->date_start);
                $end   = \Carbon\Carbon::parse($this->date_end);
                if ($start->diffInDays($end) > 62) {
                    $this->merge(['_range_too_long' => true]);
                }
            } catch (\Throwable) {
                // let the date rules report the format problem
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('_range_too_long')) {
                $validator->errors()->add('date_end', 'La période ne peut pas dépasser 62 jours.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'crm_class_id.required'       => 'Veuillez sélectionner une classe.',
            'date_start.required'         => 'Veuillez indiquer la date de début.',
            'date_end.required'           => 'Veuillez indiquer la date de fin.',
            'date_end.after_or_equal'     => 'La date de fin doit être postérieure ou égale à la date de début.',
            'attached_month.required'     => 'Veuillez indiquer le mois de rattachement.',
            'attached_month.between'      => 'Le mois de rattachement doit être compris entre 1 et 12.',
            'attached_year.required'      => 'Veuillez indiquer l’année de rattachement.',
            'group_month_number.required' => 'Veuillez indiquer le numéro de mois du groupe (ex. Mois 1).',
            'group_month_number.between'  => 'Le numéro de mois du groupe doit être compris entre 1 et 60.',
            'base_price.required'         => 'Veuillez indiquer le prix de base par étudiant.',
            'base_price.numeric'          => 'Le prix de base doit être un nombre.',
        ];
    }
}
