<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use Illuminate\Database\Seeder;

/**
 * Horaires hebdomadaires. day_of_week : 0 = lundi ... 6 = dimanche.
 */
class BusinessHourSeeder extends Seeder
{
    public function run(): void
    {
        $hours = [
            0 => ['is_open' => false, 'open_time' => null, 'close_time' => null], // lundi : fermé
            1 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'], // mardi
            2 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'], // mercredi
            3 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'], // jeudi
            4 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'], // vendredi
            5 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'], // samedi
            6 => ['is_open' => false, 'open_time' => null, 'close_time' => null], // dimanche : fermé
        ];

        foreach ($hours as $day => $attributes) {
            BusinessHour::query()->updateOrCreate(['day_of_week' => $day], $attributes);
        }
    }
}
