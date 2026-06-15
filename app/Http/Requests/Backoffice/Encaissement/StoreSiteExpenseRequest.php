<?php

namespace App\Http\Requests\Backoffice\Encaissement;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => 'required|exists:sites,id',
            'type'    => 'required|in:' . implode(',', array_keys(\App\Models\SiteExpense::TYPES)),
            'label'   => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0.01',
            'month'   => 'required|date',
            'notes'   => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required' => 'Veuillez sélectionner un centre.',
            'type.required' => 'Le type de charge est obligatoire.',
            'label.required' => 'La description est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'month.required' => 'Le mois est obligatoire.',
        ];
    }
}
