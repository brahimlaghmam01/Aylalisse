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

            // Bandeau supérieur — modifiable depuis /admin/apparence.
            'top_banner_enabled' => ['value' => true, 'type' => 'boolean'],
            'top_banner_text' => ['value' => 'Haute coiffure & lissage d’exception — Diagnostic personnalisé offert', 'type' => 'string'],
            'top_banner_subtext' => ['value' => null, 'type' => 'string'],

            // Image de la section Hero — chemin relatif sur le disque "public".
            'hero_image' => ['value' => null, 'type' => 'string'],

            // Grille tarifaire — modifiable depuis /admin/tarifs.
            'pricing_enabled' => ['value' => true, 'type' => 'boolean'],
            'pricing_title' => ['value' => 'Nos tarifs', 'type' => 'string'],
            'pricing_intro' => ['value' => 'Des tarifs clairs, adaptés à la longueur de vos cheveux. Le diagnostic capillaire est toujours offert.', 'type' => 'string'],
        ];

        foreach ($settings as $key => $setting) {
            // On ne (re)pose que les clés absentes : re-seeder en production
            // ne doit jamais réécraser un réglage déjà ajusté depuis l'admin.
            if (Setting::query()->where('key', $key)->exists()) {
                continue;
            }

            Setting::set($key, $setting['value'], $setting['type']);
        }
    }
}
