<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Seuls les formats raster sûrs sont acceptés (jamais de SVG :
            // peut embarquer du JavaScript). 6 Mo max pour un visuel Hero.
            'hero_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ];
    }

    public function attributes(): array
    {
        return [
            'hero_image' => 'image de la section Hero',
        ];
    }
}
