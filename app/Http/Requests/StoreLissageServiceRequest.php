<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLissageServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', 'unique:lissage_services,slug'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            // Note : pour une prestation "sur devis" (price = 0), l'acompte
            // peut être supérieur au prix affiché, d'où l'absence de règle lte:price.
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:600'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'short_description' => 'description courte',
            'description' => 'description',
            'price' => 'prix',
            'deposit_amount' => 'acompte',
            'duration_minutes' => 'durée (minutes)',
            'buffer_minutes' => 'tampon (minutes)',
            'image' => 'image',
            'is_active' => 'statut actif',
            'sort_order' => 'ordre d’affichage',
        ];
    }
}
