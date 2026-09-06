<?php

namespace Tests\Feature\Admin;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Services\AppointmentAvailabilityService;

class AdminAppointmentTest extends AdminFeatureTestCase
{
    public function test_appointments_list_is_only_accessible_to_an_authenticated_admin(): void
    {
        $this->get('/admin/rendez-vous')->assertRedirect(route('admin.login'));

        $response = $this->actingAs($this->admin(), 'admin')->get('/admin/rendez-vous');
        $response->assertOk();
    }

    public function test_the_appointment_detail_page_shows_first_name_last_name_and_hair_diagnosis(): void
    {
        $admin = $this->admin();
        $client = Client::factory()->create(['first_name' => 'Camille', 'last_name' => 'Rousseau', 'full_name' => 'Camille Rousseau']);
        $appointment = Appointment::factory()->for($this->signatureSoyeux(), 'lissageService')->for($client)->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
            'hair_length' => 'mi-longs',
            'chemical_history' => ['decoloration-balayage'],
            'hair_notes' => 'Précisions cheveux : mèches très claires.',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.appointments.show', $appointment));

        $response->assertOk()
            ->assertSee('Camille')
            ->assertSee('Rousseau')
            ->assertSee('Cheveux mi-longs')
            ->assertSee('Méchés / Balayage')
            ->assertSee('Précisions cheveux : mèches très claires.');
    }

    public function test_admin_can_confirm_a_pending_appointment(): void
    {
        $admin = $this->admin();
        $appointment = Appointment::factory()->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.appointments.show', $appointment))
            ->patch(route('admin.appointments.status', $appointment), ['status' => 'confirmed']);

        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $this->assertSame(AppointmentStatus::Confirmed, $appointment->fresh()->status);
    }

    public function test_cancelling_an_appointment_sets_cancelled_at(): void
    {
        $admin = $this->admin();
        $appointment = Appointment::factory()->confirmed()->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.status', $appointment), ['status' => 'cancelled']);

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::Cancelled, $appointment->status);
        $this->assertNotNull($appointment->cancelled_at);
    }

    public function test_cancelling_an_appointment_frees_its_slot(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $date = $this->nextOpenDate();

        $appointment = Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $availability = app(AppointmentAvailabilityService::class);
        $this->assertFalse($availability->isSlotAvailable($date, '09:00', $service));

        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.status', $appointment), ['status' => 'cancelled']);

        $this->assertTrue($availability->isSlotAvailable($date, '09:00', $service));
    }

    public function test_an_invalid_status_transition_is_rejected(): void
    {
        $admin = $this->admin();
        $appointment = Appointment::factory()->cancelled()->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        // "cancelled" ne peut pas redevenir "completed".
        $this->actingAs($admin, 'admin')->patch(route('admin.appointments.status', $appointment), ['status' => 'completed']);

        $this->assertSame(AppointmentStatus::Cancelled, $appointment->fresh()->status);
    }

    public function test_reschedule_checks_availability_and_succeeds_on_a_free_slot(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $date = $this->nextOpenDate();

        $appointment = Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.appointments.reschedule', $appointment), [
            'appointment_date' => $date->toDateString(),
            'start_time' => '13:00',
        ]);

        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $appointment->refresh();
        $this->assertSame('13:00:00', $appointment->start_time);
        $this->assertSame('16:30:00', $appointment->end_time);
    }

    public function test_reschedule_excludes_the_appointment_itself_from_the_conflict_check(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $date = $this->nextOpenDate();

        $appointment = Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        // Reprogrammer sur son propre créneau actuel doit réussir (pas de
        // conflit avec lui-même grâce à excludeAppointmentId).
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.appointments.reschedule', $appointment), [
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00',
        ]);

        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $this->assertSame('09:00:00', $appointment->fresh()->start_time);
    }

    public function test_reschedule_onto_a_conflicting_slot_is_refused(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $date = $this->nextOpenDate();

        $toReschedule = Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '16:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.appointments.show', $toReschedule))
            ->patch(route('admin.appointments.reschedule', $toReschedule), [
                'appointment_date' => $date->toDateString(),
                'start_time' => '13:00',
            ]);

        $response->assertRedirect(route('admin.appointments.show', $toReschedule));
        $response->assertSessionHas('error');
        $this->assertSame('09:00:00', $toReschedule->fresh()->start_time);
    }
}
