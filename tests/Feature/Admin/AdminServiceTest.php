<?php

namespace Tests\Feature\Admin;

use App\Models\Appointment;
use App\Models\LissageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminServiceTest extends AdminFeatureTestCase
{
    public function test_admin_can_create_a_service_with_length_pricing_and_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin(), 'admin')->post(route('admin.services.store'), [
            'name' => 'Lissage indien / brésilien',
            'short_description' => 'Lissage longue tenue, tarif selon la longueur.',
            'price' => 0,
            'price_courts' => '80',
            'price_mi_longs' => '90',
            'price_longs' => '100',
            'deposit_amount' => 30,
            'duration_minutes' => 180,
            'buffer_minutes' => 30,
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('lissage.jpg'),
        ])->assertRedirect(route('admin.services.index'));

        $service = LissageService::where('name', 'Lissage indien / brésilien')->firstOrFail();
        $this->assertTrue($service->hasLengthPricing());
        $this->assertSame(80.0, $service->priceForLength('courts'));
        $this->assertFalse($service->is_on_quote);
        Storage::disk('public')->assertExists($service->image);
    }

    public function test_clearing_a_length_price_field_disables_it(): void
    {
        $service = LissageService::factory()->create(['price' => 150, 'price_courts' => 80, 'price_mi_longs' => 90, 'price_longs' => 100]);

        $this->actingAs($this->admin(), 'admin')->put(route('admin.services.update', $service), [
            'name' => $service->name,
            'short_description' => $service->short_description,
            'price' => 150,
            'price_courts' => '85',
            'price_mi_longs' => '',
            'price_longs' => '',
            'deposit_amount' => 40,
            'duration_minutes' => 180,
            'is_active' => '1',
        ])->assertRedirect();

        $service->refresh();
        $this->assertSame('85.00', $service->price_courts);
        $this->assertNull($service->price_mi_longs);
        $this->assertSame(150.0, $service->priceForLength('mi-longs'));
    }

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
