<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminCalendarController extends Controller
{
    public function index(): View
    {
        return view('admin.calendar.index');
    }

    /**
     * Flux JSON consommé par FullCalendar. "start"/"end" sont fournis par
     * FullCalendar lui-même selon la vue affichée (mois/semaine/jour) — on
     * ne charge donc jamais l'historique complet, seulement la période
     * visible, bornée par sécurité à 120 jours.
     */
    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $start = Carbon::parse($validated['start'])->startOfDay();
        $end = Carbon::parse($validated['end'])->endOfDay();

        if ($start->diffInDays($end) > 120) {
            $end = $start->copy()->addDays(120);
        }

        $appointments = Appointment::query()
            ->with(['client', 'lissageService'])
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $events = $appointments->map(function (Appointment $appointment) {
            $date = $appointment->appointment_date->toDateString();

            return [
                'id' => $appointment->id,
                'title' => $appointment->client->full_name.' — '.$appointment->lissageService->name,
                'start' => "{$date}T{$appointment->start_time}",
                'end' => "{$date}T{$appointment->end_time}",
                'url' => route('admin.appointments.show', $appointment),
                'backgroundColor' => $this->colorFor($appointment->status),
                'borderColor' => $this->colorFor($appointment->status),
                'extendedProps' => [
                    'detailUrl' => route('admin.appointments.show', $appointment),
                    'status' => $appointment->status->label(),
                    'reference' => $appointment->reference,
                ],
            ];
        });

        return response()->json($events);
    }

    private function colorFor(AppointmentStatus $status): string
    {
        return match ($status) {
            AppointmentStatus::Pending => '#a98674',
            AppointmentStatus::Confirmed => '#3a251c',
            AppointmentStatus::Completed => '#cdae9c',
            AppointmentStatus::Cancelled => '#b45454',
            AppointmentStatus::NoShow => '#9a938d',
        };
    }
}
