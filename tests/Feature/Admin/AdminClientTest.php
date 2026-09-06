<?php

namespace Tests\Feature\Admin;

use App\Models\Appointment;
use App\Models\Client;

class AdminClientTest extends AdminFeatureTestCase
{
    public function test_client_search_finds_by_name_or_phone(): void
    {
        $admin = $this->admin();
        Client::factory()->create(['first_name' => 'Camille', 'last_name' => 'Rousseau', 'full_name' => 'Camille Rousseau', 'phone' => '0611111111']);
        Client::factory()->create(['first_name' => 'Sarah', 'last_name' => 'Moreau', 'full_name' => 'Sarah Moreau', 'phone' => '0622222222']);

        $response = $this->actingAs($admin, 'admin')->get('/admin/clientes?q=Camille');

        $response->assertOk()->assertSee('Camille Rousseau')->assertDontSee('Sarah Moreau');

        $responseByPhone = $this->actingAs($admin, 'admin')->get('/admin/clientes?q=0622222222');
        $responseByPhone->assertOk()->assertSee('Sarah Moreau')->assertDontSee('Camille Rousseau');
    }

    public function test_client_history_is_displayed_on_her_profile(): void
    {
        $admin = $this->admin();
        $client = Client::factory()->create(['first_name' => 'Camille', 'last_name' => 'Rousseau', 'full_name' => 'Camille Rousseau']);

        Appointment::factory()->for($this->signatureSoyeux(), 'lissageService')->for($client)->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.clients.show', $client));

        $response->assertOk()
            ->assertSee('Camille Rousseau')
            ->assertSee('Lissage Signature Soyeux');
    }
}
