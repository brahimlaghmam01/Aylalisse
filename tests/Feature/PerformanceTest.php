<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\LissageService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BusinessHourSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->seed(LissageServiceSeeder::class);
    }

    /**
     * Vérifie l'absence de N+1 évident sur les pages admin critiques : le
     * nombre de requêtes ne doit pas croître avec le nombre de rendez-vous
     * affichés (ce qui serait le signe d'un accès lazy à une relation dans
     * une boucle Blade).
     */
    public function test_dashboard_and_appointment_list_do_not_scale_queries_with_row_count(): void
    {
        $admin = Admin::factory()->create();
        $service = LissageService::first();

        $date = Carbon::today()->addDays(5);
        while (! in_array($date->dayOfWeekIso, [2, 3, 4, 5, 6], true)) {
            $date->addDay();
        }

        Client::factory()->count(15)->create()->each(function (Client $client, int $index) use ($service, $date) {
            Appointment::factory()->for($service, 'lissageService')->for($client)->create([
                'appointment_date' => $date->toDateString(),
                'start_time' => sprintf('%02d:00:00', 8 + $index),
                'end_time' => sprintf('%02d:00:00', 9 + $index),
            ]);
        });

        DB::enableQueryLog();
        $this->actingAs($admin, 'admin')->get('/admin/rendez-vous')->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::flushQueryLog();

        // Une poignée fixe de requêtes (pagination, eager loads groupés,
        // session/CSRF) — pas une par rendez-vous affiché.
        $this->assertLessThan(15, $queryCount, "La liste des rendez-vous a exécuté {$queryCount} requêtes pour 15 lignes : signe probable d'un N+1.");

        DB::enableQueryLog();
        $this->actingAs($admin, 'admin')->get('/admin')->assertOk();
        $dashboardQueryCount = count(DB::getQueryLog());
        DB::flushQueryLog();

        $this->assertLessThan(15, $dashboardQueryCount, "Le tableau de bord a exécuté {$dashboardQueryCount} requêtes : signe probable d'un N+1.");
    }
}
