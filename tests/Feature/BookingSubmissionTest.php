<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\LissageService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingSubmissionTest extends TestCase
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

    protected function validPayload(Carbon $date, array $overrides = []): array
    {
        return array_merge([
            'lissage_service_id' => $this->service->id,
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00',
            'first_name' => 'Camille',
            'last_name' => 'Rousseau',
            'phone' => '0612345678',
            'email' => 'camille@example.com',
            'hair_length' => 'mi-longs',
            'chemical_history' => ['coloration'],
            'hair_notes' => 'Cheveux sensibilisés sur les longueurs.',
        ], $overrides);
    }

    public function test_a_valid_booking_creates_an_appointment(): void
    {
        $date = $this->nextOpenDate();

        $response = $this->postJson('/reservation', $this->validPayload($date));

        $response->assertCreated()->assertJsonStructure(['reference', 'redirect']);
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_the_booking_gets_a_unique_reference(): void
    {
        $date = $this->nextOpenDate();

        $first = $this->postJson('/reservation', $this->validPayload($date, ['start_time' => '09:00']))->json('reference');
        $second = $this->postJson('/reservation', $this->validPayload($date, [
            'start_time' => '13:00',
            'phone' => '0699999999',
        ]))->json('reference');

        $this->assertNotSame($first, $second);
        $this->assertMatchesRegularExpression('/^AYL-\d{8}-[A-Z0-9]{6}$/', $first);
        $this->assertMatchesRegularExpression('/^AYL-\d{8}-[A-Z0-9]{6}$/', $second);
    }

    public function test_the_initial_status_is_pending(): void
    {
        $date = $this->nextOpenDate();

        $reference = $this->postJson('/reservation', $this->validPayload($date))->json('reference');

        $appointment = Appointment::where('reference', $reference)->firstOrFail();
        $this->assertSame('pending', $appointment->status->value);
    }

    public function test_first_name_and_last_name_are_stored_correctly(): void
    {
        $date = $this->nextOpenDate();

        $reference = $this->postJson('/reservation', $this->validPayload($date))->json('reference');

        $client = Appointment::where('reference', $reference)->firstOrFail()->client;

        $this->assertSame('Camille', $client->first_name);
        $this->assertSame('Rousseau', $client->last_name);
        $this->assertSame('Camille Rousseau', $client->full_name);
    }

    public function test_the_appointment_is_correctly_linked_to_its_client(): void
    {
        $date = $this->nextOpenDate();

        $reference = $this->postJson('/reservation', $this->validPayload($date))->json('reference');
        $appointment = Appointment::where('reference', $reference)->firstOrFail();

        $this->assertNotNull($appointment->client_id);
        $this->assertSame('0612345678', $appointment->client->phone);
        $this->assertSame('camille@example.com', $appointment->client->email);
    }

    public function test_phone_number_still_deduplicates_clients(): void
    {
        $date = $this->nextOpenDate();

        $first = $this->postJson('/reservation', $this->validPayload($date, ['start_time' => '09:00']))->json('reference');
        $second = $this->postJson('/reservation', $this->validPayload($date, ['start_time' => '13:00']))->json('reference');

        $firstClient = Appointment::where('reference', $first)->firstOrFail()->client_id;
        $secondClient = Appointment::where('reference', $second)->firstOrFail()->client_id;

        $this->assertSame($firstClient, $secondClient);
        $this->assertSame(1, Client::count());
    }

    public function test_booking_a_slot_that_just_became_unavailable_returns_the_correct_message(): void
    {
        $date = $this->nextOpenDate();

        // Une autre cliente vient de réserver ce créneau.
        $this->postJson('/reservation', $this->validPayload($date, [
            'start_time' => '09:00',
            'phone' => '0600000001',
        ]))->assertCreated();

        $response = $this->postJson('/reservation', $this->validPayload($date, [
            'start_time' => '09:00',
            'phone' => '0600000002',
            'first_name' => 'Sarah',
            'last_name' => 'Moreau',
        ]));

        $response->assertStatus(409)->assertJson([
            'message' => "Ce créneau horaire n'est plus disponible. Veuillez en choisir un autre.",
        ]);

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_personal_data_sent_in_a_conflicting_request_is_not_persisted_anywhere(): void
    {
        // Le formulaire simplifié étant monopage, la "conservation" des
        // informations après une erreur de créneau est gérée côté client
        // (aucun rechargement de page) ; côté serveur, on vérifie qu'une
        // tentative en conflit n'a aucun effet de bord (pas de cliente
        // fantôme créée pour une réservation refusée).
        $date = $this->nextOpenDate();

        $this->postJson('/reservation', $this->validPayload($date, [
            'start_time' => '09:00',
            'phone' => '0600000001',
        ]))->assertCreated();

        $this->postJson('/reservation', $this->validPayload($date, [
            'start_time' => '09:00',
            'phone' => '0600000009',
            'first_name' => 'Sarah',
            'last_name' => 'Moreau',
        ]))->assertStatus(409);

        // La cliente refusée n'a jamais été créée puisque la vérification
        // de disponibilité échoue avant la recherche/création de la cliente.
        $this->assertSame(0, Client::where('phone', '0600000009')->count());
    }

    public function test_confirmation_page_works_with_a_valid_reference(): void
    {
        $date = $this->nextOpenDate();

        $reference = $this->postJson('/reservation', $this->validPayload($date))->json('reference');

        $this->get("/reservation/confirmation/{$reference}")
            ->assertOk()
            ->assertSee($reference)
            ->assertSee('Votre demande est bien enregistrée.');
    }

    public function test_an_unknown_reference_returns_404(): void
    {
        $this->get('/reservation/confirmation/AYL-00000000-ZZZZZZ')->assertNotFound();
    }
}
