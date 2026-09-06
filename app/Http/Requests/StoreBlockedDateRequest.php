<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlockedDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'unique:blocked_dates,date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'date',
            'reason' => 'motif',
        ];
    }

    public function messages(): array
    {
        return [
            'date.unique' => 'Cette date est déjà bloquée.',
        ];
    }
}
