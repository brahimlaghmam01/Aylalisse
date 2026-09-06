<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Réservation — lus en direct par AppointmentAvailabilityService.
            'booking_interval' => ['value' => 30, 'type' => 'integer'],
            'minimum_booking_notice_hours' => ['value' => 24, 'type' => 'integer'],
            'maximum_booking_days' => ['value' => 90, 'type' => 'integer'],
            'default_buffer_minutes' => ['value' => 30, 'type' => 'integer'],

            // Informations générales — valeurs initiales reprises de
            // config/aylalisse.php, modifiables ensuite depuis /admin/parametres.
            'brand_phone' => ['value' => config('aylalisse.phone'), 'type' => 'string'],
            'brand_whatsapp' => ['value' => config('aylalisse.whatsapp'), 'type' => 'string'],
            'brand_email' => ['value' => config('aylalisse.email'), 'type' => 'string'],
            'brand_instagram' => ['value' => config('aylalisse.instagram'), 'type' => 'string'],
            'brand_address_line' => ['value' => config('aylalisse.address.line'), 'type' => 'string'],
            'brand_address_zip' => ['value' => config('aylalisse.address.zip'), 'type' => 'string'],
            'brand_address_city' => ['value' => config('aylalisse.address.city'), 'type' => 'string'],
        ];

        foreach ($settings as $key => $setting) {
            Setting::set($key, $setting['value'], $setting['type']);
        }
    }
}
