<?php

namespace App\Http\Controllers;

use App\Models\LissageService;
use App\Models\Setting;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Endpoints JSON consommés par le parcours de réservation (Alpine.js).
 * Toute la logique de disponibilité vient de AppointmentAvailabilityService :
 * le frontend n'effectue jamais son propre calcul de disponibilité.
 */
class BookingAvailabilityController extends Controller
{
    public function __construct(
        protected AppointmentAvailabilityService $availability,
    ) {}

    /**
     * Statut (réservable ou non, et pourquoi) de chaque jour d'un mois donné,
     * pour une prestation précise — sa durée + son tampon déterminent si un
     * jour a effectivement au moins un créneau disponible.
     */
    public function dates(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service' => ['required', 'string', Rule::exists('lissage_services', 'slug')->where('is_active', true)],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $service = LissageService::query()->active()->where('slug', $data['service'])->firstOrFail();

        // On borne raisonnablement la navigation pour éviter un calcul sur
        // un mois arbitrairement lointain.
        $monthStart = Carbon::createFromFormat('Y-m-d', $data['month'].'-01')->startOfMonth();
        if ($monthStart->lt(Carbon::today()->startOfMonth()) || $monthStart->gt(Carbon::today()->addYear())) {
            abort(422, 'Mois hors de la période de réservation.');
        }

        $days = [];
        $cursor = $monthStart->copy();
        $monthEnd = $monthStart->copy()->endOfMonth();

        while ($cursor->lte($monthEnd)) {
            $days[] = $this->dayStatus($cursor->copy(), $service);
            $cursor->addDay();
        }

        $maxDays = (int) Setting::get('maximum_booking_days', 90);

        return response()->json([
            'month' => $data['month'],
            'min_date' => Carbon::today()->toDateString(),
            'max_date' => Carbon::today()->addDays($maxDays)->toDateString(),
            'days' => $days,
        ]);
    }

    /**
     * @return array{date: string, bookable: bool, reason: string|null}
     */
    protected function dayStatus(Carbon $date, LissageService $service): array
    {
        if ($date->lt(Carbon::today())) {
            return ['date' => $date->toDateString(), 'bookable' => false, 'reason' => 'past'];
        }

        if ($this->availability->isDateBlocked($date)) {
            return ['date' => $date->toDateString(), 'bookable' => false, 'reason' => 'blocked'];
        }

        if (! $this->availability->isSalonOpen($date)) {
            return ['date' => $date->toDateString(), 'bookable' => false, 'reason' => 'closed'];
        }

        if (! $this->availability->isDateBookable($date)) {
            return ['date' => $date->toDateString(), 'bookable' => false, 'reason' => 'unavailable'];
        }

        $hasSlots = $this->availability->getAvailableSlots($date, $service) !== [];

        return [
            'date' => $date->toDateString(),
            'bookable' => $hasSlots,
            'reason' => $hasSlots ? null : 'full',
        ];
    }

    /**
     * Créneaux (disponibles et complets) d'une date précise, pour l'étape 3.
     */
    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service' => ['required', 'string', Rule::exists('lissage_services', 'slug')->where('is_active', true)],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $service = LissageService::query()->active()->where('slug', $data['service'])->firstOrFail();
        $date = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();

        return response()->json([
            'date' => $data['date'],
            'service' => [
                'slug' => $service->slug,
                'name' => $service->name,
                'duration_minutes' => $service->duration_minutes,
            ],
            'slots' => $this->availability->getAllSlots($date, $service),
        ]);
    }
}
