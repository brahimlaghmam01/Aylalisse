<?php

namespace Tests\Feature;

use App\Models\LissageService;
use Database\Seeders\LissageServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_page_returns_200(): void
    {
        $this->get('/reservation')->assertOk();
    }

    public function test_active_services_are_available_on_the_page(): void
    {
        $this->seed(LissageServiceSeeder::class);

        $this->get('/reservation')
            ->assertOk()
            ->assertSee('Lissage Signature Soyeux')
            ->assertSee('Lissage Premium Miroir');
    }

    public function test_inactive_services_are_not_proposed(): void
    {
        $this->seed(LissageServiceSeeder::class);

        $hidden = LissageService::where('slug', 'lissage-premium-miroir')->firstOrFail();
        $hidden->update(['is_active' => false]);

        $this->get('/reservation')
            ->assertOk()
            ->assertSee('Lissage Signature Soyeux')
            ->assertDontSee('Lissage Premium Miroir');
    }
}
