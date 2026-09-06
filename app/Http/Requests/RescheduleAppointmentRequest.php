<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'lissage_service_id' => ['nullable', 'integer', Rule::exists('lissage_services', 'id')],
        ];
    }

    public function attributes(): array
    {
        return [
            'appointment_date' => 'date',
            'start_time' => 'créneau horaire',
            'lissage_service_id' => 'prestation',
        ];
    }
}
