<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'is_open' => ['required', 'boolean'],
            'open_time' => ['required_if:is_open,1', 'nullable', 'date_format:H:i'],
            'close_time' => ['required_if:is_open,1', 'nullable', 'date_format:H:i', 'after:open_time'],
        ];
    }

    public function attributes(): array
    {
        return [
            'day_of_week' => 'jour de la semaine',
            'is_open' => 'salon ouvert',
            'open_time' => "heure d'ouverture",
            'close_time' => 'heure de fermeture',
        ];
    }

    public function messages(): array
    {
        return [
            'close_time.after' => "L'heure de fermeture doit être postérieure à l'heure d'ouverture.",
        ];
    }
}
