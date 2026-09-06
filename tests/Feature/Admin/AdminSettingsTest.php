<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;

class AdminSettingsTest extends AdminFeatureTestCase
{
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'brand_phone' => '+33 1 42 00 00 00',
            'brand_whatsapp' => '+33 6 00 00 00 00',
            'brand_email' => 'contact@aylalisse.fr',
            'brand_instagram' => 'https://instagram.com/aylalisse',
            'brand_address_line' => '18 rue du Faubourg Saint-Honoré',
            'brand_address_zip' => '75008',
            'brand_address_city' => 'Paris',
            'booking_interval' => 30,
            'minimum_booking_notice_hours' => 24,
            'maximum_booking_days' => 90,
            'default_buffer_minutes' => 30,
        ], $overrides);
    }

    public function test_changing_booking_interval_really_changes_the_generated_slots(): void
    {
        $admin = $this->admin();
        $date = $this->nextOpenDate();

        $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), $this->validPayload(['booking_interval' => 15]));

        $this->assertSame(15, Setting::get('booking_interval'));

        $response = $this->getJson('/reservation/availability/slots?'.http_build_query([
            'service' => $this->signatureSoyeux()->slug,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();
        $starts = collect($response->json('slots'))->pluck('start')->take(3)->values();
        $this->assertSame(['09:00', '09:15', '09:30'], $starts->all());
    }

    public function test_settings_page_requires_authentication(): void
    {
        $this->get('/admin/parametres')->assertRedirect(route('admin.login'));
    }
}
