<?php

namespace App\Http\Requests;

use App\Support\LissageServicePricingInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLissageServiceRequest extends FormRequest
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
        $service = $this->route('lissage_service');
        $serviceId = is_object($service) ? $service->id : $service;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:170', Rule::unique('lissage_services', 'slug')->ignore($serviceId)],
            'short_description' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_courts' => ['nullable', 'numeric', 'min:0'],
            'price_mi_longs' => ['nullable', 'numeric', 'min:0'],
            'price_longs' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'duration_minutes' => ['sometimes', 'required', 'integer', 'min:15', 'max:600'],
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
