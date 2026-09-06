<?php

namespace Tests\Feature\Admin;

use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Support\Carbon;

class AdminRevenueTest extends AdminFeatureTestCase
{
    public function test_the_dashboard_shows_the_revenue_section(): void
    {
        Appointment::factory()->completed()->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => Carbon::today()->toDateString(),
            'price' => 240,
            'deposit_amount' => 50,
            'remaining_amount' => 190,
            'deposit_paid_at' => now(),
        ]);

        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.dashboard', ['period' => 'month']))
            ->assertOk();

        $response->assertSee('affaires', false)
            ->assertSee('Encaissé')
            ->assertSee('encaisser', false)
            ->assertSee('Acomptes enregistrés')
            ->assertSee('240', false)   // CA
            ->assertSee('Revenus par prestation');
    }

    public function test_the_period_filter_is_validated(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.dashboard', ['period' => 'bogus']))
            ->assertSessionHasErrors('period');
    }

    public function test_admin_can_mark_the_deposit_and_balance_as_paid(): void
    {
        $appointment = Appointment::factory()->completed()->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => Carbon::today()->toDateString(),
            'price' => 240, 'deposit_amount' => 50, 'remaining_amount' => 190,
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->from(route('admin.appointments.show', $appointment))
            ->patch(route('admin.appointments.payment', $appointment), ['deposit_paid' => '1', 'balance_paid' => '1'])
            ->assertRedirect();

        $appointment->refresh();
        $this->assertTrue($appointment->isDepositPaid());
        $this->assertTrue($appointment->isBalancePaid());
        $this->assertSame(240.0, $appointment->amountCollected());
        $this->assertSame(0.0, $appointment->amountOutstanding());
    }

    public function test_unchecking_a_payment_clears_its_timestamp(): void
    {
        $appointment = Appointment::factory()->completed()->depositPaid()->balancePaid()
            ->for($this->signatureSoyeux(), 'lissageService')->create([
                'appointment_date' => Carbon::today()->toDateString(),
            ]);

        $this->actingAs($this->admin(), 'admin')
            ->patch(route('admin.appointments.payment', $appointment), ['deposit_paid' => '1']) // balance_paid absent
            ->assertRedirect();

        $appointment->refresh();
        $this->assertTrue($appointment->isDepositPaid());
        $this->assertFalse($appointment->isBalancePaid());
    }

    public function test_a_completed_appointment_is_not_collected_until_marked(): void
    {
        $appointment = Appointment::factory()->completed()->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => Carbon::today()->toDateString(),
            'price' => 240, 'deposit_amount' => 50, 'remaining_amount' => 190,
        ]);

        $this->assertSame(0.0, $appointment->amountCollected());
        $this->assertSame(240.0, $appointment->amountOutstanding());
    }

    public function test_the_client_page_shows_a_financial_summary(): void
    {
        $client = Client::factory()->create(['first_name' => 'Camille', 'last_name' => 'Rousseau', 'full_name' => 'Camille Rousseau']);

        // 1 terminé payé intégralement, 1 terminé partiellement, 1 en attente
        Appointment::factory()->completed()->for($client)->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => Carbon::today()->subMonth()->toDateString(),
            'price' => 240, 'deposit_amount' => 50, 'remaining_amount' => 190,
            'deposit_paid_at' => now(), 'balance_paid_at' => now(),
        ]);
        Appointment::factory()->completed()->for($client)->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => Carbon::today()->subWeek()->toDateString(),
            'price' => 240, 'deposit_amount' => 50, 'remaining_amount' => 190,
            'deposit_paid_at' => now(),
        ]);
        Appointment::factory()->for($client)->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => Carbon::today()->addWeek()->toDateString(),
            'price' => 240, 'deposit_amount' => 50, 'remaining_amount' => 190,
        ]);

        $response = $this->actingAs($this->admin(), 'admin')->get(route('admin.clients.show', $client));

        $response->assertOk()
            ->assertSee('Valeur des prestations terminées')
            ->assertSee('Réellement encaissé')
            ->assertSee('Restant à encaisser')
            ->assertSee('480', false)   // 2 completed × 240
            ->assertSee('Historique financier des rendez-vous');
    }

    public function test_a_sur_devis_appointment_shows_sur_devis_not_zero_euro(): void
    {
        $appointment = Appointment::factory()->onQuote()->for($this->signatureSoyeux(), 'lissageService')->create([
            'appointment_date' => Carbon::today()->toDateString(),
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Sur devis')
            ->assertSee('À définir après diagnostic');
    }
}
