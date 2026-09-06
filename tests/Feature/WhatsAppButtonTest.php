<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_button_uses_the_configured_setting_value(): void
    {
        Setting::set('brand_whatsapp', '+33 6 12 34 56 78', 'string');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('https://wa.me/33612345678', false);
    }

    public function test_the_booking_page_uses_a_reservation_specific_message(): void
    {
        Setting::set('brand_whatsapp', '+33 6 12 34 56 78', 'string');

        $response = $this->get('/reservation');

        $response->assertOk();
        $response->assertSee(rawurlencode("Bonjour AylaLisse, j'ai une question concernant ma réservation."), false);
    }
}
