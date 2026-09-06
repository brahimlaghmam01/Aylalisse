<?php

namespace Database\Factories;

use App\Models\LissageService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LissageService>
 */
class LissageServiceFactory extends Factory
{
    protected $model = LissageService::class;

    public function definition(): array
    {
        $name = 'Lissage '.fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomElement([240, 310, 360]),
            'deposit_amount' => 50,
            'duration_minutes' => fake()->randomElement([210, 240, 270]),
            'buffer_minutes' => 30,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
