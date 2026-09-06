<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockedDateRequest;
use App\Http\Requests\StoreBlockedTimeRangeRequest;
use App\Http\Requests\StoreBusinessHoursRequest;
use App\Models\BlockedDate;
use App\Models\BlockedTimeRange;
use App\Models\BusinessHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAvailabilityController extends Controller
{
    /**
     * Cette page pilote directement les données lues par
     * AppointmentAvailabilityService : toute modification ici a un effet
     * immédiat sur le booking public, sans aucune intervention manuelle.
     */
    public function index(): View
    {
        $businessHours = BusinessHour::query()->orderBy('day_of_week')->get();
        $blockedDates = BlockedDate::query()->orderBy('date')->get();
        $blockedTimeRanges = BlockedTimeRange::query()->orderBy('date')->orderBy('start_time')->get();

        return view('admin.availability.index', compact('businessHours', 'blockedDates', 'blockedTimeRanges'));
    }

    public function updateBusinessHour(StoreBusinessHoursRequest $request, BusinessHour $businessHour): RedirectResponse
    {
        $data = $request->validated();

        if (! ($data['is_open'] ?? false)) {
            $data['open_time'] = null;
            $data['close_time'] = null;
        }

        $businessHour->update($data);

        return back()->with('success', 'Les disponibilités ont été mises à jour.');
    }

    public function storeBlockedDate(StoreBlockedDateRequest $request): RedirectResponse
    {
        BlockedDate::create($request->validated());

        return back()->with('success', 'La date bloquée a été ajoutée.');
    }

    public function destroyBlockedDate(BlockedDate $blockedDate): RedirectResponse
    {
        $blockedDate->delete();

        return back()->with('success', 'La date bloquée a été supprimée.');
    }

    public function storeBlockedTimeRange(StoreBlockedTimeRangeRequest $request): RedirectResponse
    {
        BlockedTimeRange::create($request->validated());

        return back()->with('success', 'La plage horaire bloquée a été ajoutée.');
    }

    public function destroyBlockedTimeRange(BlockedTimeRange $blockedTimeRange): RedirectResponse
    {
        $blockedTimeRange->delete();

        return back()->with('success', 'La plage horaire bloquée a été supprimée.');
    }
}
