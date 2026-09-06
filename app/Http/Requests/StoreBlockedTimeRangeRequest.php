<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlockedTimeRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'heure de début',
            'end_time' => 'heure de fin',
            'reason' => 'motif',
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => "L'heure de fin doit être postérieure à l'heure de début.",
        ];
    }
}
