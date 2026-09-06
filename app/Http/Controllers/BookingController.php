<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotUnavailableException;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\LissageService;
use App\Services\AppointmentNotifier;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Page de réservation : le parcours en 5 étapes (Alpine.js), alimenté
     * par les prestations actives réellement en base.
     */
    public function index(): View
    {
        $services = LissageService::query()->active()->ordered()->get();

        return view('booking.index', [
            'servicesPayload' => $services->map(fn (LissageService $service) => [
                'id' => $service->id,
                'slug' => $service->slug,
                'name' => $service->name,
                'short_description' => $service->short_description,
                'duration_minutes' => $service->duration_minutes,
                'duration_label' => $this->formatDuration($service->duration_minutes),
                'price' => (float) $service->price,
                'price_label' => $service->is_on_quote ? 'Sur devis' : $this->formatMoney((float) $service->price),
                'deposit_amount' => (float) $service->deposit_amount,
                'deposit_label' => $this->formatMoney((float) $service->deposit_amount),
                'is_on_quote' => $service->is_on_quote,
            ])->values(),
        ]);
    }

    /**
     * Crée le rendez-vous. La disponibilité est revalidée côté serveur par
     * AppointmentService — le frontend n'est jamais la source de vérité.
     */
    public function store(StoreAppointmentRequest $request, AppointmentService $appointmentService, AppointmentNotifier $notifier): JsonResponse
    {
        try {
            $appointment = $appointmentService->createAppointment($request->validated());
        } catch (SlotUnavailableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        }

        // Le rendez-vous est déjà créé et validé à ce stade (la transaction
        // de AppointmentService a déjà été validée) : un échec d'envoi
        // d'e-mail ne doit jamais annuler la réservation — voir AppointmentNotifier.
        $notifier->notifyNewAppointment($appointment);

        return response()->json([
            'reference' => $appointment->reference,
            'redirect' => route('booking.confirmation', ['reference' => $appointment->reference]),
        ], 201);
    }

    public function confirmation(string $reference): View
    {
        $appointment = Appointment::query()
            ->with(['lissageService', 'client'])
            ->where('reference', $reference)
            ->firstOrFail();

        return view('booking.confirmation', ['appointment' => $appointment]);
    }

    /**
     * "3h30", "4h", "4h30"...
     */
    private function formatDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $rest === 0 ? "{$hours}h" : "{$hours}h".str_pad((string) $rest, 2, '0', STR_PAD_LEFT);
    }

    /**
     * "240 €", "49,50 €"...
     */
    private function formatMoney(float $amount): string
    {
        $formatted = floor($amount) === $amount
            ? number_format($amount, 0, ',', ' ')
            : number_format($amount, 2, ',', ' ');

        return $formatted.' €';
    }
}
