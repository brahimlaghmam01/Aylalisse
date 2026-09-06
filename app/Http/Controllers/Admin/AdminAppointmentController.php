<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\LissageService;
use App\Services\AppointmentAvailabilityService;
use App\Services\AppointmentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAppointmentController extends Controller
{
    /**
     * Liste des rendez-vous avec recherche, filtres et pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'string'],
            'service' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:150'],
        ]);

        $appointments = Appointment::query()
            ->with(['client', 'lissageService'])
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('appointment_date', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['service'] ?? null, fn ($query, $service) => $query->where('lissage_service_id', $service))
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->whereHas('client', function ($clientQuery) use ($q) {
                    $clientQuery->where('full_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(20)
            ->withQueryString();

        $services = LissageService::query()->ordered()->get();
        $statuses = AppointmentStatus::cases();

        return view('admin.appointments.index', compact('appointments', 'services', 'statuses', 'filters'));
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['client', 'lissageService']);
        $services = LissageService::query()->ordered()->get();

        return view('admin.appointments.show', compact('appointment', 'services'));
    }

    /**
     * Confirme, termine, annule ou marque une cliente absente — en
     * respectant les transitions de statut autorisées.
     */
    public function updateStatus(UpdateAppointmentStatusRequest $request, Appointment $appointment, AppointmentNotifier $notifier): RedirectResponse
    {
        $target = $request->targetStatus();

        if (! $appointment->status->canTransitionTo($target)) {
            return back()->with('error', "Impossible de passer du statut « {$appointment->status->label()} » à « {$target->label()} ».");
        }

        $appointment->status = $target;

        if ($target === AppointmentStatus::Cancelled) {
            $appointment->cancelled_at = now();
        }

        $appointment->save();

        if ($target === AppointmentStatus::Confirmed) {
            $notifier->notifyConfirmed($appointment);
        } elseif ($target === AppointmentStatus::Cancelled) {
            $notifier->notifyCancelled($appointment);
        }

        return back()->with('success', match ($target) {
            AppointmentStatus::Confirmed => 'Le rendez-vous a été confirmé.',
            AppointmentStatus::Completed => 'Le rendez-vous a été marqué comme terminé.',
            AppointmentStatus::Cancelled => 'Le rendez-vous a été annulé.',
            AppointmentStatus::NoShow => 'Le rendez-vous a été marqué comme absence.',
            default => 'Le statut du rendez-vous a été mis à jour.',
        });
    }

    /**
     * Reprogramme un rendez-vous (date, heure et éventuellement prestation),
     * en revalidant la disponibilité côté serveur — jamais uniquement côté
     * frontend — via AppointmentAvailabilityService::isSlotAvailable()
     * avec exclusion du rendez-vous actuel.
     */
    public function reschedule(
        RescheduleAppointmentRequest $request,
        Appointment $appointment,
        AppointmentAvailabilityService $availability,
        AppointmentNotifier $notifier
    ): RedirectResponse {
        $service = $request->filled('lissage_service_id')
            ? LissageService::findOrFail($request->integer('lissage_service_id'))
            : $appointment->lissageService;

        $date = Carbon::parse($request->string('appointment_date'));
        $startTime = $request->string('start_time');

        if (! $availability->isSlotAvailable($date, $startTime, $service, $appointment->id)) {
            return back()
                ->withInput()
                ->with('error', "Ce créneau horaire n'est plus disponible. Veuillez en choisir un autre.");
        }

        $previousDate = $appointment->appointment_date->toDateString();
        $previousStartTime = $appointment->start_time;

        DB::transaction(function () use ($appointment, $service, $date, $startTime) {
            $start = Carbon::parse($date->toDateString().' '.$startTime);
            $end = $start->copy()->addMinutes($service->duration_minutes);

            $appointment->lissage_service_id = $service->id;
            $appointment->appointment_date = $date->toDateString();
            $appointment->start_time = $start->format('H:i:s');
            $appointment->end_time = $end->format('H:i:s');
            $appointment->price = $service->price;
            $appointment->deposit_amount = $service->deposit_amount;
            $appointment->remaining_amount = max((float) $service->price - (float) $service->deposit_amount, 0);
            $appointment->save();
        });

        $notifier->notifyRescheduled($appointment, $previousDate, $previousStartTime);

        return redirect()->route('admin.appointments.show', $appointment)->with('success', 'Le rendez-vous a été reprogrammé.');
    }

    public function updateNotes(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ], [], ['admin_notes' => 'notes internes']);

        $appointment->update($validated);

        return back()->with('success', 'Les notes internes ont été enregistrées.');
    }
}
