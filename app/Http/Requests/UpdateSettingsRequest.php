<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Informations générales
            'brand_phone' => ['required', 'string', 'max:30'],
            'brand_whatsapp' => ['required', 'string', 'max:30'],
            'brand_email' => ['required', 'email', 'max:150'],
            'brand_instagram' => ['nullable', 'url', 'max:255'],
            'brand_address_line' => ['required', 'string', 'max:255'],
            'brand_address_zip' => ['required', 'string', 'max:20'],
            'brand_address_city' => ['required', 'string', 'max:150'],

            // Paramètres de réservation — ont un effet réel et immédiat
            // sur AppointmentAvailabilityService.
            'booking_interval' => ['required', 'integer', 'min:5', 'max:120'],
            'minimum_booking_notice_hours' => ['required', 'integer', 'min:0', 'max:240'],
            'maximum_booking_days' => ['required', 'integer', 'min:1', 'max:365'],
            'default_buffer_minutes' => ['required', 'integer', 'min:0', 'max:240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'brand_phone' => 'téléphone',
            'brand_whatsapp' => 'WhatsApp',
            'brand_email' => 'e-mail',
            'brand_instagram' => 'Instagram',
            'brand_address_line' => 'adresse',
            'brand_address_zip' => 'code postal',
            'brand_address_city' => 'ville',
            'booking_interval' => 'intervalle des créneaux',
            'minimum_booking_notice_hours' => 'délai minimum de réservation',
            'maximum_booking_days' => 'horizon maximum de réservation',
            'default_buffer_minutes' => 'tampon par défaut',
        ];
    }
}
