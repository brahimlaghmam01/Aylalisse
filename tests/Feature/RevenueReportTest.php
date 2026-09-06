<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\LissageService;
use App\Services\RevenueReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Cohérence financière du tableau de bord (spécification "Revenus &
 * chiffre d'affaires").
 */
class RevenueReportTest extends TestCase
{
    use RefreshDatabase;

    private function appointment(array $attributes = []): Appointment
    {
        return Appointment::factory()->create(array_merge([
            'appointment_date' => Carbon::today()->toDateString(),
            'price' => 240,
            'deposit_amount' => 50,
            'remaining_amount' => 190,
        ], $attributes));
    }

    public function test_pending_is_not_counted_as_realised_revenue(): void
    {
        $this->appointment(['status' => 'pending']);

        $this->assertSame(0.0, (new RevenueReport('all'))->revenue());
    }

    public function test_confirmed_is_not_a_completed_prestation(): void
    {
        $this->appointment(['status' => 'confirmed']);

        $report = new RevenueReport('all');
        $this->assertSame(0.0, $report->revenue());
        $this->assertSame(0, $report->completedCount());
    }

    public function test_cancelled_is_never_counted(): void
    {
        $this->appointment(['status' => 'cancelled', 'cancelled_at' => now()]);

        $report = new RevenueReport('all');
        $this->assertSame(0.0, $report->revenue());
        $this->assertSame(0.0, $report->deposits());
    }

    public function test_no_show_is_not_a_completed_prestation(): void
    {
        $this->appointment(['status' => 'no_show']);

        $this->assertSame(0.0, (new RevenueReport('all'))->revenue());
    }

    public function test_completed_is_counted_in_revenue(): void
    {
        $this->appointment(['status' => 'completed']);

        $report = new RevenueReport('all');
        $this->assertSame(240.0, $report->revenue());
        $this->assertSame(1, $report->completedCount());
    }

    public function test_revenue_uses_the_historical_price_even_after_the_service_price_changes(): void
    {
        $service = LissageService::factory()->create(['price' => 240]);
        $appt = $this->appointment(['status' => 'completed', 'lissage_service_id' => $service->id, 'price' => 240]);

        // L'administratrice augmente le tarif de la prestation plus tard.
        $service->update(['price' => 320]);

        $this->assertSame(240.0, (new RevenueReport('all'))->revenue());
        $this->assertSame('240.00', $appt->fresh()->price);
    }

    public function test_deposits_are_aggregated_across_non_cancelled_appointments(): void
    {
        $this->appointment(['status' => 'pending', 'deposit_amount' => 50]);
        $this->appointment(['status' => 'completed', 'deposit_amount' => 60]);
        $this->appointment(['status' => 'cancelled', 'cancelled_at' => now(), 'deposit_amount' => 70]);

        $this->assertSame(110.0, (new RevenueReport('all'))->deposits());
    }

    public function test_an_unpaid_balance_is_not_considered_collected(): void
    {
        // completed, acompte payé, solde NON payé
        $this->appointment(['status' => 'completed'])->update(['deposit_paid_at' => now()]);

        $report = new RevenueReport('all');
        $this->assertSame(50.0, $report->collected());
        $this->assertSame(190.0, $report->outstanding());
    }

    public function test_a_paid_balance_is_added_to_the_collected_amount(): void
    {
        $appt = $this->appointment(['status' => 'completed']);
        $appt->update(['deposit_paid_at' => now(), 'balance_paid_at' => now()]);

        $report = new RevenueReport('all');
        $this->assertSame(240.0, $report->collected());
        $this->assertSame(0.0, $report->outstanding());
    }

    public function test_period_filtering_returns_the_right_amounts(): void
    {
        $this->appointment(['status' => 'completed', 'appointment_date' => Carbon::today()->toDateString(), 'price' => 100]);
        $this->appointment(['status' => 'completed', 'appointment_date' => Carbon::today()->subMonths(2)->toDateString(), 'price' => 200]);

        $this->assertSame(100.0, (new RevenueReport('month'))->revenue());
        $this->assertSame(300.0, (new RevenueReport('all'))->revenue());
    }

    public function test_revenue_by_service_uses_historical_prices(): void
    {
        $a = LissageService::factory()->create(['name' => 'Lissage indien', 'price' => 90]);
        $b = LissageService::factory()->create(['name' => 'Lissage botox', 'price' => 120]);

        $this->appointment(['status' => 'completed', 'lissage_service_id' => $a->id, 'price' => 90]);
        $this->appointment(['status' => 'completed', 'lissage_service_id' => $a->id, 'price' => 80]); // longueur différente
        $this->appointment(['status' => 'completed', 'lissage_service_id' => $b->id, 'price' => 120]);

        $a->update(['price' => 999]); // ne doit pas influer

        $byService = collect((new RevenueReport('all'))->byService())->keyBy('name');

        $this->assertSame(2, $byService['Lissage indien']['count']);
        $this->assertSame(170.0, $byService['Lissage indien']['revenue']);
        $this->assertSame(120.0, $byService['Lissage botox']['revenue']);
    }

    public function test_final_check_scenario(): void
    {
        // Prix 240 / acompte 50 / solde 190
        $pending = $this->appointment(['status' => 'pending']);

        // pending : CA réalisé = 0
        $this->assertSame(0.0, (new RevenueReport('all'))->revenue());

        // acompte du pending marqué payé => encaissé = 50
        $pending->update(['deposit_paid_at' => now()]);
        $this->assertSame(50.0, (new RevenueReport('all'))->collected());

        // Un rendez-vous devient "completed" : CA réalisé = 240
        $completed = $this->appointment(['status' => 'completed']);
        $this->assertSame(240.0, (new RevenueReport('all'))->revenue());

        // completed, acompte payé, solde non payé => encaissé += 50, à encaisser = 190
        $completed->update(['deposit_paid_at' => now()]);
        $this->assertSame(100.0, (new RevenueReport('all'))->collected());
        $this->assertSame(190.0, (new RevenueReport('all'))->outstanding());

        // solde payé => encaissé += 190, à encaisser = 0
        $completed->update(['balance_paid_at' => now()]);
        $this->assertSame(290.0, (new RevenueReport('all'))->collected());
        $this->assertSame(0.0, (new RevenueReport('all'))->outstanding());
    }
}
