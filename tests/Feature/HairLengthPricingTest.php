<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\LissageService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Tarification par longueur de cheveux et intégrité du prix historique.
 */
class HairLengthPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BusinessHourSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    protected function openDate(): Carbon
    {
        $date = Carbon::today()->addDays(6);
        while (! in_array($date->dayOfWeekIso, [2, 3, 4, 5, 6], true)) {
            $date->addDay();
        }

        return $date;
    }

    protected function lengthPricedService(): LissageService
    {
        return LissageService::factory()->create([
            'name' => 'Lissage indien / brésilien',
            'price' => 0,
            'price_courts' => 80,
            'price_mi_longs' => 90,
            'price_longs' => 100,
            'deposit_amount' => 30,
            'duration_minutes' => 180,
            'buffer_minutes' => 30,
            'is_active' => true,
        ]);
    }

    public function test_model_returns_the_price_for_the_selected_length(): void
    {
        $service = $this->lengthPricedService();

        $this->assertTrue($service->hasLengthPricing());
        $this->assertSame(80.0, $service->priceForLength('courts'));
        $this->assertSame(90.0, $service->priceForLength('mi-longs'));
        $this->assertSame(100.0, $service->priceForLength('longs'));
    }

    public function test_flat_priced_service_ignores_length(): void
    {
        $service = LissageService::factory()->create(['price' => 150, 'price_courts' => null, 'price_mi_longs' => null, 'price_longs' => null]);

        $this->assertFalse($service->hasLengthPricing());
        $this->assertSame(150.0, $service->priceForLength('courts'));
        $this->assertSame(150.0, $service->priceForLength(null));
    }

    public function test_a_partially_priced_service_falls_back_to_the_flat_price(): void
    {
        $service = LissageService::factory()->create(['price' => 150, 'price_courts' => 80, 'price_mi_longs' => null, 'price_longs' => null]);

        $this->assertSame(80.0, $service->priceForLength('courts'));
        $this->assertSame(150.0, $service->priceForLength('mi-longs'));
    }

    public function test_booking_stores_the_price_matching_the_selected_length(): void
    {
        $service = $this->lengthPricedService();

        $response = $this->postJson('/reservation', [
            'lissage_service_id' => $service->id,
            'appointment_date' => $this->openDate()->toDateString(),
            'start_time' => '09:00',
            'first_name' => 'Nadia',
            'last_name' => 'Kessa',
            'phone' => '0612345678',
            'email' => 'nadia@example.com',
            'hair_length' => 'longs',
        ]);

        $response->assertCreated();

        $appointment = Appointment::firstOrFail();
        $this->assertSame('100.00', $appointment->price);
        $this->assertSame('30.00', $appointment->deposit_amount);
        $this->assertSame('70.00', $appointment->remaining_amount);
    }

    public function test_hair_length_is_required_when_the_service_is_length_priced(): void
    {
        $service = $this->lengthPricedService();

        $response = $this->postJson('/reservation', [
            'lissage_service_id' => $service->id,
            'appointment_date' => $this->openDate()->toDateString(),
            'start_time' => '09:00',
            'first_name' => 'Nadia',
            'last_name' => 'Kessa',
            'phone' => '0612345678',
            'email' => 'nadia@example.com',
            // pas de hair_length
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('hair_length');
    }

    public function test_hair_length_stays_optional_for_flat_priced_services(): void
    {
        $service = LissageService::factory()->create(['price' => 150, 'is_active' => true, 'duration_minutes' => 180, 'buffer_minutes' => 30]);

        $response = $this->postJson('/reservation', [
            'lissage_service_id' => $service->id,
            'appointment_date' => $this->openDate()->toDateString(),
            'start_time' => '09:00',
            'first_name' => 'Nadia',
            'last_name' => 'Kessa',
            'phone' => '0612345678',
            'email' => 'nadia@example.com',
        ]);

        $response->assertCreated();
        $this->assertSame('150.00', Appointment::firstOrFail()->price);
    }

    public function test_rescheduling_time_only_never_changes_the_historical_price(): void
    {
        $admin = Admin::factory()->create();
        $service = LissageService::factory()->create(['price' => 240, 'deposit_amount' => 50, 'duration_minutes' => 180, 'buffer_minutes' => 30, 'is_active' => true]);
        $date = $this->openDate();

        $appointment = Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'price' => 240,
            'deposit_amount' => 50,
            'remaining_amount' => 190,
        ]);

        // Le tarif de la prestation augmente APRÈS la réservation.
        $service->update(['price' => 300, 'deposit_amount' => 70]);

        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.reschedule', $appointment), [
            'appointment_date' => $date->toDateString(),
            'start_time' => '13:00',
        ])->assertRedirect();

        $appointment->refresh();
        $this->assertSame('13:00:00', $appointment->start_time);
        $this->assertSame('240.00', $appointment->price, 'Le prix historique ne doit pas suivre le nouveau tarif.');
        $this->assertSame('50.00', $appointment->deposit_amount);
        $this->assertSame('190.00', $appointment->remaining_amount);
    }

    public function test_rescheduling_onto_a_different_service_reprices_deliberately(): void
    {
        $admin = Admin::factory()->create();
        $from = LissageService::factory()->create(['price' => 240, 'deposit_amount' => 50, 'duration_minutes' => 180, 'buffer_minutes' => 30, 'is_active' => true]);
        $to = LissageService::factory()->create(['price' => 0, 'price_courts' => 80, 'price_mi_longs' => 90, 'price_longs' => 100, 'deposit_amount' => 30, 'duration_minutes' => 180, 'buffer_minutes' => 30, 'is_active' => true]);
        $date = $this->openDate();

        $appointment = Appointment::factory()->for($from, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'price' => 240,
            'deposit_amount' => 50,
            'remaining_amount' => 190,
            'hair_length' => 'mi-longs',
        ]);

        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.reschedule', $appointment), [
            'lissage_service_id' => $to->id,
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00',
        ])->assertRedirect();

        $appointment->refresh();
        $this->assertSame($to->id, $appointment->lissage_service_id);
        // Nouvelle prestation, tarifée par longueur : mi-longs => 90.
        $this->assertSame('90.00', $appointment->price);
        $this->assertSame('30.00', $appointment->deposit_amount);
        $this->assertSame('60.00', $appointment->remaining_amount);
    }
}
