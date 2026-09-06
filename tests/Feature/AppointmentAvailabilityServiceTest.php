<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BlockedDate;
use App\Models\BlockedTimeRange;
use App\Models\LissageService;
use App\Services\AppointmentAvailabilityService;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\LissageServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AppointmentAvailabilityService $availability;

    protected LissageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BusinessHourSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->seed(LissageServiceSeeder::class);

        $this->availability = app(AppointmentAvailabilityService::class);
        $this->service = LissageService::where('slug', 'lissage-signature-soyeux')->firstOrFail();
    }

    /**
     * Prochaine date ouverte (mardi à samedi), à au moins $minDaysAhead jours,
     * pour rester confortablement au-delà du délai minimum de réservation (24h).
     */
    protected function nextOpenDate(int $minDaysAhead = 5): Carbon
    {
        $date = Carbon::today()->addDays($minDaysAhead);

        while (! in_array($date->dayOfWeekIso, [2, 3, 4, 5, 6], true)) {
            $date->addDay();
        }

        return $date;
    }

    protected function nextClosedDate(): Carbon
    {
        $date = Carbon::today()->addDays(5);

        while ($date->dayOfWeekIso !== 1) { // lundi
            $date->addDay();
        }

        return $date;
    }

    public function test_slot_is_available_when_salon_open_and_no_conflict(): void
    {
        $date = $this->nextOpenDate();

        $this->assertTrue($this->availability->isSlotAvailable($date, '09:00', $this->service));
    }

    public function test_slot_is_unavailable_once_it_is_booked(): void
    {
        $date = $this->nextOpenDate();

        Appointment::factory()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $this->assertFalse($this->availability->isSlotAvailable($date, '09:00', $this->service));
    }

    public function test_partially_overlapping_slot_is_detected_as_conflict(): void
    {
        $date = $this->nextOpenDate();

        // 09:00 -> 12:30 (fin réelle), tampon jusqu'à 13:00
        Appointment::factory()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        // Une nouvelle demande à 11:00 chevauche largement le rendez-vous existant.
        $this->assertFalse($this->availability->isSlotAvailable($date, '11:00', $this->service));
    }

    public function test_buffer_time_after_appointment_is_respected(): void
    {
        $date = $this->nextOpenDate();

        Appointment::factory()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00', // + 30 min de tampon => bloqué jusqu'à 13:00
        ]);

        $this->assertFalse($this->availability->isSlotAvailable($date, '12:30', $this->service));
        $this->assertTrue($this->availability->isSlotAvailable($date, '13:00', $this->service));
    }

    public function test_blocked_date_has_no_available_slots(): void
    {
        $date = $this->nextOpenDate();

        BlockedDate::create(['date' => $date->toDateString(), 'reason' => 'Congés']);

        $this->assertFalse($this->availability->isDateBookable($date));
        $this->assertSame([], $this->availability->getAvailableSlots($date, $this->service));
    }

    public function test_blocked_time_range_removes_overlapping_slots(): void
    {
        $date = $this->nextOpenDate();

        BlockedTimeRange::create([
            'date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'reason' => 'Intervention technique',
        ]);

        $this->assertFalse($this->availability->isSlotAvailable($date, '09:00', $this->service));
        $this->assertTrue($this->availability->isSlotAvailable($date, '13:00', $this->service));
    }

    public function test_salon_closed_day_returns_no_slots(): void
    {
        $monday = $this->nextClosedDate();

        $this->assertFalse($this->availability->isSalonOpen($monday));
        $this->assertSame([], $this->availability->getAvailableSlots($monday, $this->service));
    }

    public function test_cancelled_appointment_no_longer_blocks_its_slot(): void
    {
        $date = $this->nextOpenDate();

        Appointment::factory()->cancelled()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $this->assertTrue($this->availability->isSlotAvailable($date, '09:00', $this->service));
    }

    public function test_pending_appointment_blocks_its_slot(): void
    {
        $date = $this->nextOpenDate();

        Appointment::factory()->for($this->service, 'lissageService')->create([
            'status' => AppointmentStatus::Pending,
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $this->assertFalse($this->availability->isSlotAvailable($date, '09:00', $this->service));
    }

    public function test_confirmed_appointment_blocks_its_slot(): void
    {
        $date = $this->nextOpenDate();

        Appointment::factory()->confirmed()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $this->assertFalse($this->availability->isSlotAvailable($date, '09:00', $this->service));
    }

    public function test_completed_and_no_show_appointments_do_not_block_availability(): void
    {
        $date = $this->nextOpenDate();

        Appointment::factory()->completed()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        Appointment::factory()->noShow()->for($this->service, 'lissageService')->create([
            'appointment_date' => $date->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '17:30:00',
        ]);

        $this->assertTrue($this->availability->isSlotAvailable($date, '09:00', $this->service));
        $this->assertTrue($this->availability->isSlotAvailable($date, '14:00', $this->service));
    }

    public function test_slot_within_minimum_notice_window_is_rejected(): void
    {
        // minimum_booking_notice_hours = 24 (SettingSeeder). On fige "maintenant"
        // un mardi à 08:00 : le créneau du même jour à 09:00 (dans 1h) est bien
        // en deçà du délai de préavis. Temps figé => test déterministe quel que
        // soit le jour d'exécution.
        Carbon::setTestNow(Carbon::parse('2026-09-08 08:00:00')); // mardi

        $sameDay = Carbon::parse('2026-09-08');

        $this->assertFalse($this->availability->isSlotAvailable($sameDay, '09:00', $this->service));

        Carbon::setTestNow();
    }

    public function test_get_available_slots_excludes_out_of_hours_start(): void
    {
        $date = $this->nextOpenDate();

        $slots = $this->availability->getAvailableSlots($date, $this->service);

        // 3h30 de prestation + 30 min de tampon = 4h ; le dernier départ
        // possible avant la fermeture (18:00) est donc 14:00.
        $starts = array_column($slots, 'start');

        $this->assertContains('09:00', $starts);
        $this->assertContains('14:00', $starts);
        $this->assertNotContains('14:30', $starts);
    }
}
