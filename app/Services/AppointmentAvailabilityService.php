<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BlockedDate;
use App\Models\BlockedTimeRange;
use App\Models\BusinessHour;
use App\Models\LissageService;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Moteur central de disponibilité des rendez-vous.
 *
 * Toute la logique de créneaux (horaires d'ouverture, dates et plages
 * bloquées, durée + tampon des prestations, conflits avec les rendez-vous
 * existants, délai minimum de réservation) est centralisée ici — jamais
 * dans un contrôleur, une vue ou une route.
 */
class AppointmentAvailabilityService
{
    /**
     * Une date est réservable si elle n'est pas dans le passé, pas au-delà
     * de l'horizon maximum, pas entièrement bloquée, et si le salon y est ouvert.
     */
    public function isDateBookable(Carbon $date): bool
    {
        $day = $date->copy()->startOfDay();

        if ($day->lt(Carbon::today())) {
            return false;
        }

        $maxDays = (int) Setting::get('maximum_booking_days', 90);
        if ($day->gt(Carbon::today()->addDays($maxDays))) {
            return false;
        }

        if ($this->isDateBlocked($day)) {
            return false;
        }

        return $this->isSalonOpen($day);
    }

    public function isDateBlocked(Carbon $date): bool
    {
        return BlockedDate::query()->whereDate('date', $date->toDateString())->exists();
    }

    public function isSalonOpen(Carbon $date): bool
    {
        return $this->openingHoursFor($date) !== null;
    }

    /**
     * Retourne les horaires d'ouverture du jour, ou null si le salon est fermé
     * ce jour-là (fermeture hebdomadaire ou horaires non configurés).
     */
    public function openingHoursFor(Carbon $date): ?BusinessHour
    {
        $businessHour = BusinessHour::query()
            ->where('day_of_week', $this->dayOfWeekIndex($date))
            ->first();

        if (! $businessHour || ! $businessHour->is_open || ! $businessHour->open_time || ! $businessHour->close_time) {
            return null;
        }

        return $businessHour;
    }

    /**
     * Convertit le jour de semaine Carbon (ISO : 1 = lundi ... 7 = dimanche)
     * vers la convention métier (0 = lundi ... 6 = dimanche).
     */
    protected function dayOfWeekIndex(Carbon $date): int
    {
        return $date->dayOfWeekIso - 1;
    }

    /**
     * Génère tous les créneaux candidats de la journée (dans les horaires
     * d'ouverture, au pas de "booking_interval"), chacun marqué disponible
     * ou non. Source unique de vérité, utilisée à la fois pour l'API des
     * créneaux (qui peut afficher des créneaux "COMPLET") et pour
     * getAvailableSlots() (qui ne garde que les disponibles).
     *
     * L'heure de fin affichée est la fin réelle de la prestation — le
     * tampon interne qui suit n'est jamais exposé à la cliente, seulement
     * pris en compte dans le calcul de conflit.
     *
     * @return array<int, array{start: string, end: string, available: bool}>
     */
    public function getAllSlots(Carbon $date, LissageService $service): array
    {
        if (! $this->isDateBookable($date)) {
            return [];
        }

        $businessHour = $this->openingHoursFor($date);
        if (! $businessHour) {
            return [];
        }

        $interval = max(5, (int) Setting::get('booking_interval', 30));
        $serviceDuration = $service->duration_minutes;
        $totalDuration = $serviceDuration + $service->buffer_minutes;

        $openTime = $this->combine($date, $businessHour->open_time);
        $closeTime = $this->combine($date, $businessHour->close_time);
        $earliestBookable = Carbon::now()->addHours((int) Setting::get('minimum_booking_notice_hours', 24));

        $blockedRanges = $this->blockedRangesFor($date);
        $existingAppointments = $this->blockingAppointmentsFor($date);

        $slots = [];
        $cursor = $openTime->copy();

        while ($cursor->copy()->addMinutes($totalDuration)->lte($closeTime)) {
            $slotStart = $cursor->copy();
            $serviceEnd = $slotStart->copy()->addMinutes($serviceDuration);
            $blockingEnd = $slotStart->copy()->addMinutes($totalDuration);

            $isAvailable = $slotStart->gte($earliestBookable)
                && ! $this->overlapsBlockedRanges($slotStart, $blockingEnd, $blockedRanges)
                && ! $this->overlapsAppointments($slotStart, $blockingEnd, $existingAppointments);

            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $serviceEnd->format('H:i'),
                'available' => $isAvailable,
            ];

            $cursor->addMinutes($interval);
        }

        return $slots;
    }

    /**
     * Génère la liste des créneaux réellement disponibles pour une
     * prestation, sur une date donnée.
     *
     * @return array<int, array{start: string, end: string, available: bool}>
     */
    public function getAvailableSlots(Carbon $date, LissageService $service): array
    {
        return array_values(array_filter(
            $this->getAllSlots($date, $service),
            fn (array $slot) => $slot['available'],
        ));
    }

    /**
     * Vérifie qu'un créneau précis est encore disponible. Utilisée côté
     * serveur juste avant la création d'un rendez-vous : le frontend n'est
     * jamais considéré comme une source fiable.
     */
    public function isSlotAvailable(
        Carbon $date,
        string $startTime,
        LissageService $service,
        ?int $excludeAppointmentId = null
    ): bool {
        if (! $this->isDateBookable($date)) {
            return false;
        }

        $businessHour = $this->openingHoursFor($date);
        if (! $businessHour) {
            return false;
        }

        $totalDuration = $service->duration_minutes + $service->buffer_minutes;

        $slotStart = $this->combine($date, $startTime);
        $blockingEnd = $slotStart->copy()->addMinutes($totalDuration);

        $openTime = $this->combine($date, $businessHour->open_time);
        $closeTime = $this->combine($date, $businessHour->close_time);

        if ($slotStart->lt($openTime) || $blockingEnd->gt($closeTime)) {
            return false;
        }

        $minNoticeHours = (int) Setting::get('minimum_booking_notice_hours', 24);
        if ($slotStart->lt(Carbon::now()->addHours($minNoticeHours))) {
            return false;
        }

        if ($this->overlapsBlockedRanges($slotStart, $blockingEnd, $this->blockedRangesFor($date))) {
            return false;
        }

        $existing = $this->blockingAppointmentsFor($date, $excludeAppointmentId);

        return ! $this->overlapsAppointments($slotStart, $blockingEnd, $existing);
    }

    /**
     * @return Collection<int, BlockedTimeRange>
     */
    protected function blockedRangesFor(Carbon $date): Collection
    {
        return BlockedTimeRange::query()
            ->whereDate('date', $date->toDateString())
            ->get();
    }

    /**
     * Rendez-vous "pending"/"confirmed" du jour, avec la prestation associée
     * (nécessaire pour connaître le tampon de chacun).
     *
     * @return Collection<int, Appointment>
     */
    protected function blockingAppointmentsFor(Carbon $date, ?int $excludeAppointmentId = null): Collection
    {
        return Appointment::query()
            ->onDate($date)
            ->blocking()
            ->when($excludeAppointmentId, fn ($query) => $query->whereKeyNot($excludeAppointmentId))
            ->with('lissageService:id,buffer_minutes')
            ->get(['id', 'lissage_service_id', 'appointment_date', 'start_time', 'end_time']);
    }

    protected function overlapsBlockedRanges(Carbon $start, Carbon $end, Collection $ranges): bool
    {
        foreach ($ranges as $range) {
            $rangeStart = $this->combine($start, $range->start_time);
            $rangeEnd = $this->combine($start, $range->end_time);

            if ($this->intervalsOverlap($start, $end, $rangeStart, $rangeEnd)) {
                return true;
            }
        }

        return false;
    }

    protected function overlapsAppointments(Carbon $start, Carbon $end, Collection $appointments): bool
    {
        foreach ($appointments as $appointment) {
            [$existingStart, $existingBlockingEnd] = $this->appointmentBlockingWindow($appointment);

            if ($this->intervalsOverlap($start, $end, $existingStart, $existingBlockingEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function appointmentBlockingWindow(Appointment $appointment): array
    {
        $start = $this->combine($appointment->appointment_date, $appointment->start_time);
        $buffer = $appointment->lissageService?->buffer_minutes ?? 0;
        $end = $this->combine($appointment->appointment_date, $appointment->end_time)->addMinutes($buffer);

        return [$start, $end];
    }

    /**
     * Deux intervalles [startA, endA) et [startB, endB) sont en conflit si :
     * startA < endB ET endA > startB.
     */
    protected function intervalsOverlap(Carbon $startA, Carbon $endA, Carbon $startB, Carbon $endB): bool
    {
        return $startA->lt($endB) && $endA->gt($startB);
    }

    protected function combine(Carbon $date, string $time): Carbon
    {
        return Carbon::parse($date->toDateString().' '.$time);
    }
}
