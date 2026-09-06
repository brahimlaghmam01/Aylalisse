<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::enum(AppointmentStatus::class)],
            'appointment_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'statut',
            'appointment_date' => 'date',
            'start_time' => 'créneau horaire',
            'admin_notes' => 'notes internes',
        ];
    }
}
