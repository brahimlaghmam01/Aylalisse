<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\LissageService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingAvailabilityApiTest extends TestCase
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

    public function test_slots_endpoint_returns_available_slots(): void
    {
        $date = $this->nextOpenDate();

        $response = $this->getJson('/reservation/availability/slots?'.http_build_query([
            'service' => $this->service->slug,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk()
            ->assertJsonPath('date', $date->toDateString())
            ->assertJsonPath('slots.0.start', '09:00')
            ->assertJsonPath('slots.0.end', '12:30')
            ->assertJsonPath('slots.0.available', true);
    }

    public function test_dates_endpoint_marks_blocked_date_as_not_bookable_with_no_slots(): void
    {
        $date = $this->nextOpenDate();

        BlockedDate::create(['date' => $date->toDateString(), 'reason' => 'Congés annuels']);

        $datesResponse = $this->getJson('/reservation/availability/dates?'.http_build_query([
            'service' => $this->service->slug,
            'month' => $date->format('Y-m'),
        ]));

        $datesResponse->assertOk();
        $day = collect($datesResponse->json('days'))->firstWhere('date', $date->toDateString());
        $this->assertNotNull($day);
        $this->assertFalse($day['bookable']);
        $this->assertSame('blocked', $day['reason']);

        $slotsResponse = $this->getJson('/reservation/availability/slots?'.http_build_query([
            'service' => $this->service->slug,
            'date' => $date->toDateString(),
        ]));

        $slotsResponse->assertOk()->assertJsonCount(0, 'slots');
    }

    public function test_availability_endpoints_reject_unknown_or_inactive_service(): void
    {
        $inactive = LissageService::factory()->create(['is_active' => false]);

        $this->getJson('/reservation/availability/slots?'.http_build_query([
            'service' => $inactive->slug,
            'date' => $this->nextOpenDate()->toDateString(),
        ]))->assertStatus(422);

        $this->getJson('/reservation/availability/slots?'.http_build_query([
            'service' => 'ce-service-n-existe-pas',
            'date' => $this->nextOpenDate()->toDateString(),
        ]))->assertStatus(422);
    }
}
