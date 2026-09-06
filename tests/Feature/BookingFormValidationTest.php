<?php

namespace Tests\Feature;

use App\Models\LissageService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Validation du formulaire de réservation simplifié (une seule page) :
 * longueur des cheveux et couleur des cheveux utilisent désormais des
 * valeurs différentes de l'ancien formulaire en 5 étapes.
 */
class BookingFormValidationTest extends TestCase
{
    use RefreshDatabase;

    protected LissageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BusinessHourSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->seed(LissageServiceSeeder::class);

        $this->service = LissageService::where('slug', 'lissage-signature-soyeux')->firstOrFail();
    }

    protected function nextOpenDate(int $minDaysAhead = 5): Carbon
    {
        $date = Carbon::today()->addDays($minDaysAhead);

        while (! in_array($date->dayOfWeekIso, [2, 3, 4, 5, 6], true)) {
            $date->addDay();
        }

        return $date;
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'lissage_service_id' => $this->service->id,
            'appointment_date' => $this->nextOpenDate()->toDateString(),
            'start_time' => '09:00',
            'first_name' => 'Camille',
            'last_name' => 'Rousseau',
            'phone' => '0612345678',
            'email' => 'camille@example.com',
        ], $overrides);
    }

    public function test_the_new_hair_length_values_are_accepted(): void
    {
        foreach (array_values(['courts', 'mi-longs', 'longs']) as $index => $value) {
            $response = $this->postJson('/reservation', $this->validPayload([
                'appointment_date' => $this->nextOpenDate(5 + $index * 7)->toDateString(),
                'hair_length' => $value,
                'phone' => '06'.random_int(10000000, 99999999),
            ]));

            $response->assertCreated();
        }
    }

    public function test_an_invalid_hair_length_is_rejected(): void
    {
        $response = $this->postJson('/reservation', $this->validPayload([
            'hair_length' => 'longueur-inconnue',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('hair_length');
    }

    public function test_an_old_style_hair_length_value_is_no_longer_accepted(): void
    {
        // L'ancien formulaire en 5 étapes envoyait "mi-dos" — le formulaire
        // simplifié ne le propose plus et ne doit plus l'accepter en écriture
        // (les rendez-vous historiques qui le portent restent lisibles côté admin).
        $response = $this->postJson('/reservation', $this->validPayload([
            'hair_length' => 'mi-dos',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('hair_length');
    }

    public function test_the_new_hair_color_values_are_accepted(): void
    {
        $values = [[], ['coloration'], ['decoloration-balayage'], ['decoloration'], ['autre']];

        foreach ($values as $index => $value) {
            $response = $this->postJson('/reservation', $this->validPayload([
                'appointment_date' => $this->nextOpenDate(5 + $index * 7)->toDateString(),
                'chemical_history' => $value,
                'phone' => '06'.random_int(10000000, 99999999),
            ]));

            $response->assertCreated();
        }
    }

    public function test_an_invalid_hair_color_is_rejected(): void
    {
        $response = $this->postJson('/reservation', $this->validPayload([
            'chemical_history' => ['couleur-inconnue'],
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('chemical_history.0');
    }

    public function test_email_is_now_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['email']);

        $response = $this->postJson('/reservation', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_first_name_and_last_name_are_both_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['first_name'], $payload['last_name']);

        $response = $this->postJson('/reservation', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['first_name', 'last_name']);
    }
}
