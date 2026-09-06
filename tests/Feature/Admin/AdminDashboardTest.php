<?php

namespace Tests\Feature\Admin;

use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Support\Carbon;

class AdminDashboardTest extends AdminFeatureTestCase
{
    public function test_dashboard_statistics_match_real_data(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $today = Carbon::today();

        // 1 rendez-vous aujourd'hui (confirmed), 1 pending (autre date), 3 clientes.
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $client3 = Client::factory()->create();

        Appointment::factory()->confirmed()->for($service, 'lissageService')->for($client1)->create([
            'appointment_date' => $today->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        Appointment::factory()->for($service, 'lissageService')->for($client2)->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        // client3 sans rendez-vous, comptée dans le total des clientes.

        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats) {
            return $stats['today'] === 1
                && $stats['pending'] === 1
                && $stats['confirmed'] === 1
                && $stats['clients'] === 3;
        });
    }
}
