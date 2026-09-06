<?php

namespace Tests\Feature\Admin;

use App\Models\Appointment;

class AdminCalendarTest extends AdminFeatureTestCase
{
    public function test_calendar_api_returns_appointments_within_the_requested_period(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $inRange = $this->nextOpenDate();
        $outOfRange = $this->nextOpenDate()->addYear();

        $insideAppointment = Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $inRange->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $outOfRange->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->getJson('/admin/calendrier/evenements?'.http_build_query([
            'start' => $inRange->copy()->startOfMonth()->toDateString(),
            'end' => $inRange->copy()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk();
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($insideAppointment->id));
        $this->assertCount(1, $ids);
    }

    public function test_calendar_route_requires_authentication(): void
    {
        $this->get('/admin/calendrier/evenements?start=2026-01-01&end=2026-01-31')
            ->assertRedirect(route('admin.login'));
    }
}
