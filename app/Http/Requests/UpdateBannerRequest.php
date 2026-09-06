<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'top_banner_enabled' => ['sometimes', 'boolean'],
            'top_banner_text' => ['required', 'string', 'max:180'],
            'top_banner_subtext' => ['nullable', 'string', 'max:180'],
        ];
    }

    public function attributes(): array
    {
        return [
            'top_banner_text' => 'texte du bandeau',
            'top_banner_subtext' => 'sous-texte du bandeau',
        ];
    }
}
