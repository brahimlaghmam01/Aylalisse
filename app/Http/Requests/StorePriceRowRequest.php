<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceRowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Accepte « 80 », « 80,00 » ou « 80.00 » ; champ vide => sur devis.
        if ($this->filled('price')) {
            $this->merge([
                'price' => str_replace([' ', ','], ['', '.'], (string) $this->input('price')),
            ]);
        } else {
            $this->merge(['price' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:150'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'note' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'label' => 'intitulé',
            'price' => 'prix',
            'note' => 'mention',
            'sort_order' => 'ordre d’affichage',
            'is_active' => 'activation',
        ];
    }
}
