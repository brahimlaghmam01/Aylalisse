<?php

namespace Tests\Feature\Admin;

use App\Models\BlockedDate;
use App\Models\BlockedTimeRange;
use App\Models\BusinessHour;
use App\Services\AppointmentAvailabilityService;

class AdminAvailabilityTest extends AdminFeatureTestCase
{
    public function test_changing_business_hours_immediately_impacts_the_availability_engine(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $date = $this->nextOpenDate();
        $availability = app(AppointmentAvailabilityService::class);

        $this->assertTrue($availability->isSlotAvailable($date, '09:00', $service));

        $hour = BusinessHour::where('day_of_week', $date->dayOfWeekIso - 1)->firstOrFail();

        $this->actingAs($admin, 'admin')->put(route('admin.availability.hours.update', $hour), [
            'day_of_week' => $hour->day_of_week,
            'is_open' => '1',
            'open_time' => '11:00',
            'close_time' => '18:00',
        ]);

        $this->assertFalse($availability->isSlotAvailable($date, '09:00', $service));
        $this->assertTrue($availability->isSlotAvailable($date, '11:00', $service));
    }

    public function test_a_blocked_date_immediately_prevents_booking(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $date = $this->nextOpenDate();
        $availability = app(AppointmentAvailabilityService::class);

        $this->assertTrue($availability->isDateBookable($date));

        $this->actingAs($admin, 'admin')->post(route('admin.availability.blocked-dates.store'), [
            'date' => $date->toDateString(),
            'reason' => 'Congés',
        ]);

        $this->assertTrue(BlockedDate::whereDate('date', $date->toDateString())->exists());
        $this->assertFalse($availability->isDateBookable($date));
        $this->assertFalse($availability->isSlotAvailable($date, '09:00', $service));
    }

    public function test_removing_a_blocked_date_restores_availability(): void
    {
        $admin = $this->admin();
        $date = $this->nextOpenDate();
        $blockedDate = BlockedDate::create(['date' => $date->toDateString()]);
        $availability = app(AppointmentAvailabilityService::class);

        $this->assertFalse($availability->isDateBookable($date));

        $this->actingAs($admin, 'admin')->delete(route('admin.availability.blocked-dates.destroy', $blockedDate));

        $this->assertTrue($availability->isDateBookable($date));
    }

    public function test_a_blocked_time_range_prevents_the_affected_slots(): void
    {
        $admin = $this->admin();
        $service = $this->signatureSoyeux();
        $date = $this->nextOpenDate();
        $availability = app(AppointmentAvailabilityService::class);

        $this->assertTrue($availability->isSlotAvailable($date, '09:00', $service));

        $this->actingAs($admin, 'admin')->post(route('admin.availability.blocked-ranges.store'), [
            'date' => $date->toDateString(),
            'start_time' => '09:00',
            'end_time' => '13:00',
            'reason' => 'Formation',
        ]);

        $this->assertTrue(BlockedTimeRange::whereDate('date', $date->toDateString())->exists());
        $this->assertFalse($availability->isSlotAvailable($date, '09:00', $service));
        $this->assertTrue($availability->isSlotAvailable($date, '13:00', $service));
    }
}
