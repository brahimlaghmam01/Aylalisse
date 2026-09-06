<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBeforeAfterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'before_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'after_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hair_type' => ['nullable', 'string', 'max:150'],
            'lissage_type' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_published' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'before_image' => 'image avant',
            'after_image' => 'image après',
            'hair_type' => 'type de cheveux',
            'lissage_type' => 'type de lissage',
            'description' => 'description',
            'is_published' => 'publié',
            'sort_order' => 'ordre d’affichage',
        ];
    }
}
