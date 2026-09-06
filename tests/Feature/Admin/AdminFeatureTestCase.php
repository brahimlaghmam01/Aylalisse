<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\LissageService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

abstract class AdminFeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BusinessHourSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->seed(LissageServiceSeeder::class);
    }

    protected function admin(array $overrides = []): Admin
    {
        return Admin::factory()->create($overrides);
    }

    protected function signatureSoyeux(): LissageService
    {
        return LissageService::where('slug', 'lissage-signature-soyeux')->firstOrFail();
    }

    protected function premiumMiroir(): LissageService
    {
        return LissageService::where('slug', 'lissage-premium-miroir')->firstOrFail();
    }

    protected function nextOpenDate(int $minDaysAhead = 5): Carbon
    {
        $date = Carbon::today()->addDays($minDaysAhead);

        while (! in_array($date->dayOfWeekIso, [2, 3, 4, 5, 6], true)) {
            $date->addDay();
        }

        return $date;
    }
}
