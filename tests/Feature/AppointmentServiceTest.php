<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\LissageService;
use App\Services\AppointmentService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AppointmentService $appointments;

    protected LissageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BusinessHourSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->seed(LissageServiceSeeder::class);

        $this->appointments = app(AppointmentService::class);
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

    protected function baseData(Carbon $date, array $overrides = []): array
    {
        return array_merge([
            'lissage_service_id' => $this->service->id,
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00',
            'first_name' => 'Camille',
            'last_name' => 'Rousseau',
            'phone' => '0600000001',
            'email' => 'camille@example.com',
            'hair_length' => 'mi-longs',
            'chemical_history' => ['coloration'],
            'hair_notes' => 'Cheveux sensibilisés sur les longueurs.',
        ], $overrides);
    }

    public function test_it_creates_an_appointment_with_correct_reference_and_status(): void
    {
        $date = $this->nextOpenDate();

        $appointment = $this->appointments->createAppointment($this->baseData($date));

        $this->assertMatchesRegularExpression('/^AYL-\d{8}-[A-Z0-9]{6}$/', $appointment->reference);
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertSame($this->service->id, $appointment->lissage_service_id);
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id]);
    }

    public function test_it_computes_end_time_from_service_duration(): void
    {
        $date = $this->nextOpenDate();

        // Lissage Signature Soyeux : 210 minutes (3h30)
        $appointment = $this->appointments->createAppointment($this->baseData($date, ['start_time' => '09:00']));

        $this->assertSame('12:30:00', $appointment->end_time);
    }

    public function test_it_computes_remaining_amount_from_price_and_deposit(): void
    {
        $date = $this->nextOpenDate();

        $appointment = $this->appointments->createAppointment($this->baseData($date));

        // Prix 240,00 € - acompte 50,00 € = 190,00 €
        $this->assertEquals(240.00, (float) $appointment->price);
        $this->assertEquals(50.00, (float) $appointment->deposit_amount);
        $this->assertEquals(190.00, (float) $appointment->remaining_amount);
    }

    public function test_it_reuses_existing_client_found_by_phone(): void
    {
        $date = $this->nextOpenDate();

        $first = $this->appointments->createAppointment($this->baseData($date, ['start_time' => '09:00']));
        $second = $this->appointments->createAppointment($this->baseData($date, ['start_time' => '13:00']));

        $this->assertSame($first->client_id, $second->client_id);
        $this->assertSame(1, Client::count());
    }

    public function test_it_prevents_a_second_booking_on_the_same_slot(): void
    {
        $date = $this->nextOpenDate();

        $this->appointments->createAppointment($this->baseData($date, [
            'start_time' => '09:00',
            'phone' => '0600000001',
        ]));

        $this->expectException(SlotUnavailableException::class);

        $this->appointments->createAppointment($this->baseData($date, [
            'start_time' => '09:00',
            'phone' => '0600000002',
            'first_name' => 'Sarah',
            'last_name' => 'Moreau',
        ]));
    }

    public function test_only_one_appointment_exists_after_a_conflicting_attempt(): void
    {
        $date = $this->nextOpenDate();

        $this->appointments->createAppointment($this->baseData($date, ['start_time' => '09:00']));

        try {
            $this->appointments->createAppointment($this->baseData($date, [
                'start_time' => '09:00',
                'phone' => '0600000002',
            ]));
        } catch (SlotUnavailableException) {
            // attendu
        }

        $this->assertSame(1, Appointment::query()
            ->whereDate('appointment_date', $date->toDateString())
            ->where('start_time', '09:00:00')
            ->count());
    }
}
