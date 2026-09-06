<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'titre de la section',
            'subtitle' => 'sous-titre',
            'sort_order' => 'ordre d’affichage',
            'is_active' => 'activation',
        ];
    }
}
