<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\LissageService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $date = Carbon::today()->addDays(5);
        $start = '09:00:00';
        $end = '12:30:00';

        return [
            'reference' => Appointment::generateUniqueReference($date),
            'client_id' => Client::factory(),
            'lissage_service_id' => LissageService::factory(),
            'appointment_date' => $date->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
            'status' => AppointmentStatus::Pending,
            'price' => 240,
            'deposit_amount' => 50,
            'remaining_amount' => 190,
            'hair_length' => null,
            'natural_texture' => null,
            'chemical_history' => null,
            'hair_notes' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => AppointmentStatus::Confirmed]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => AppointmentStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => AppointmentStatus::Completed]);
    }

    public function noShow(): static
    {
        return $this->state(fn () => ['status' => AppointmentStatus::NoShow]);
    }

    public function depositPaid(): static
    {
        return $this->state(fn () => ['deposit_paid_at' => now()]);
    }

    public function balancePaid(): static
    {
        return $this->state(fn () => ['balance_paid_at' => now()]);
    }

    /**
     * Prestation "sur devis" : prix nul, seul l'acompte est défini.
     */
    public function onQuote(): static
    {
        return $this->state(fn () => [
            'price' => 0,
            'deposit_amount' => 50,
            'remaining_amount' => 0,
        ]);
    }
}
