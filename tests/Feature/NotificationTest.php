<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\LissageService;
use App\Models\Setting;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentReceived;
use App\Notifications\AppointmentRescheduled;
use App\Notifications\NewAppointmentForAdmin;
use App\Services\AppointmentNotifier;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
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
        ], $overrides);
    }

    public function test_a_new_appointment_notifies_the_client(): void
    {
        Notification::fake();

        $date = $this->nextOpenDate();
        $response = $this->postJson('/reservation', $this->validPayload($date));
        $response->assertCreated();

        $client = Client::where('phone', '0612345678')->firstOrFail();

        Notification::assertSentTo($client, AppointmentReceived::class);
    }

    public function test_a_new_appointment_notifies_the_admin_at_the_configured_address(): void
    {
        Notification::fake();
        Setting::set('brand_email', 'admin-test@aylalisse.fr', 'string');

        $date = $this->nextOpenDate();
        $this->postJson('/reservation', $this->validPayload($date))->assertCreated();

        Notification::assertSentOnDemand(
            NewAppointmentForAdmin::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'admin-test@aylalisse.fr';
            }
        );
    }

    public function test_confirming_an_appointment_notifies_the_client(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();
        $appointment = Appointment::factory()->for($this->service, 'lissageService')->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.status', $appointment), ['status' => 'confirmed']);

        Notification::assertSentTo($appointment->client, AppointmentConfirmed::class);
    }

    public function test_cancelling_an_appointment_notifies_the_client(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();
        $appointment = Appointment::factory()->confirmed()->for($this->service, 'lissageService')->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.status', $appointment), ['status' => 'cancelled']);

        Notification::assertSentTo($appointment->client, AppointmentCancelled::class);
    }

    public function test_rescheduling_an_appointment_notifies_the_client(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();
        $date = $this->nextOpenDate();
        $appointment = Appointment::factory()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.reschedule', $appointment), [
            'appointment_date' => $date->toDateString(),
            'start_time' => '13:00',
        ]);

        Notification::assertSentTo($appointment->client, AppointmentRescheduled::class);
    }

    public function test_a_notification_failure_never_bubbles_up(): void
    {
        Log::shouldReceive('error')->once();

        $appointment = Appointment::factory()->for($this->service, 'lissageService')->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        // Simule une relation cliente cassée/inaccessible : notify() sur null
        // lève une erreur PHP — AppointmentNotifier doit l'avaler sans jamais
        // la laisser remonter, quelle qu'en soit la cause réelle en production
        // (SMTP indisponible, DNS en échec, etc.).
        $appointment->setRelation('client', null);

        $notifier = app(AppointmentNotifier::class);

        // Ne doit lever aucune exception : la réservation reste valide même
        // si la notification échoue totalement.
        $notifier->notifyNewAppointment($appointment);

        $this->assertTrue(true);
    }
}
