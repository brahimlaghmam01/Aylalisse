<?php

namespace App\Http\Requests;

use App\Support\LissageServicePricingInput;
use Illuminate\Foundation\Http\FormRequest;

class StoreLissageServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(LissageServicePricingInput::normalize($this));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', 'unique:lissage_services,slug'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            // Tarifs par longueur — optionnels. Dès qu'au moins un est
            // renseigné, le prix facturé à la réservation dépend de la
            // longueur choisie (le prix forfaitaire "price" sert de repli).
            'price_courts' => ['nullable', 'numeric', 'min:0'],
            'price_mi_longs' => ['nullable', 'numeric', 'min:0'],
            'price_longs' => ['nullable', 'numeric', 'min:0'],
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
            'price_courts' => 'prix cheveux courts',
            'price_mi_longs' => 'prix cheveux mi-longs',
            'price_longs' => 'prix cheveux longs',
            'deposit_amount' => 'acompte',
            'duration_minutes' => 'durée (minutes)',
            'buffer_minutes' => 'tampon (minutes)',
            'image' => 'image',
            'is_active' => 'statut actif',
            'sort_order' => 'ordre d’affichage',
        ];
    }
}
