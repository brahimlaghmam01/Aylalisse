<?php

namespace Tests\Feature\Admin;

use App\Models\Appointment;
use Illuminate\Database\QueryException;

class AdminServiceTest extends AdminFeatureTestCase
{
    public function test_deactivating_a_service_removes_it_from_public_booking(): void
    {
        $admin = $this->admin();
        $service = $this->premiumMiroir();

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.services.toggle', $service))
            ->assertRedirect();

        $this->assertFalse($service->fresh()->is_active);

        $this->get('/reservation')->assertOk()->assertDontSee('Lissage Premium Miroir');
    }

    public function test_a_service_with_historical_appointments_cannot_be_deleted_at_the_database_level(): void
    {
        $service = $this->signatureSoyeux();

        Appointment::factory()->for($service, 'lissageService')->create([
            'appointment_date' => $this->nextOpenDate()->toDateString(),
        ]);

        // Aucune route admin n'expose de suppression de prestation (seule la
        // désactivation est possible) ; on vérifie ici que la contrainte
        // restrictOnDelete protège bien la donnée historique si jamais une
        // suppression était tentée directement.
        $this->expectException(QueryException::class);

        $service->delete();
    }
}
