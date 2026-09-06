<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\LissageService;
use Illuminate\Cache\Lock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Création des rendez-vous : cliente (recherche/création), référence,
 * calculs de prix, et protection anti-double réservation.
 *
 * La logique de disponibilité elle-même reste dans AppointmentAvailabilityService ;
 * ce service orchestre la création autour d'elle.
 */
class AppointmentService
{
    public function __construct(
        protected AppointmentAvailabilityService $availability,
    ) {}

    /**
     * Crée un rendez-vous après revalidation stricte de la disponibilité.
     *
     * Double protection contre les réservations simultanées :
     *   1. Un verrou nommé par date sérialise toutes les créations de
     *      rendez-vous pour ce jour (via le cache "database", table cache_locks) ;
     *   2. Une transaction MySQL + une nouvelle vérification de disponibilité
     *      sont exécutées à l'intérieur de ce verrou avant l'insertion.
     *
     * Si deux clientes tentent de réserver le même créneau au même instant,
     * l'une des deux attend le verrou, revérifie, et reçoit une
     * SlotUnavailableException si le créneau vient d'être pris.
     *
     * @param  array{
     *     lissage_service_id: int,
     *     appointment_date: string,
     *     start_time: string,
     *     first_name: string,
     *     last_name: string,
     *     phone: string,
     *     email?: string|null,
     *     hair_length?: string|null,
     *     natural_texture?: string|null,
     *     chemical_history?: array<int, string>|null,
     *     hair_notes?: string|null,
     * }  $data
     *
     * @throws SlotUnavailableException
     */
    public function createAppointment(array $data): Appointment
    {
        $service = LissageService::query()->active()->findOrFail($data['lissage_service_id']);
        $date = Carbon::parse($data['appointment_date'])->startOfDay();
        $startTime = $data['start_time'];

        /** @var Lock $lock */
        $lock = Cache::lock($this->lockKey($date), 15);

        try {
            return $lock->block(5, function () use ($data, $service, $date, $startTime) {
                return DB::transaction(function () use ($data, $service, $date, $startTime) {
                    if (! $this->availability->isSlotAvailable($date, $startTime, $service)) {
                        throw new SlotUnavailableException;
                    }

                    $client = $this->findOrCreateClient($data);

                    return $this->insertAppointment($client, $service, $date, $startTime, $data);
                });
            });
        } catch (LockTimeoutException) {
            // Une autre réservation est en cours de traitement sur cette même
            // journée : par prudence, on refuse plutôt que de risquer un conflit.
            throw new SlotUnavailableException;
        }
    }

    protected function insertAppointment(Client $client, LissageService $service, Carbon $date, string $startTime, array $data): Appointment
    {
        $start = Carbon::parse($date->toDateString().' '.$startTime);
        $end = $start->copy()->addMinutes($service->duration_minutes);

        $price = (float) $service->price;
        $deposit = (float) $service->deposit_amount;
        $remaining = max($price - $deposit, 0);

        return Appointment::create([
            'reference' => Appointment::generateUniqueReference($date),
            'client_id' => $client->id,
            'lissage_service_id' => $service->id,
            'appointment_date' => $date->toDateString(),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'status' => AppointmentStatus::Pending,
            'price' => $price,
            'deposit_amount' => $deposit,
            'remaining_amount' => $remaining,
            'hair_length' => $data['hair_length'] ?? null,
            'natural_texture' => $data['natural_texture'] ?? null,
            'chemical_history' => $data['chemical_history'] ?? null,
            'hair_notes' => $data['hair_notes'] ?? null,
        ]);
    }

    /**
     * Recherche une cliente existante par téléphone (identifiant principal),
     * sinon en crée une nouvelle. Évite les doublons ; met à jour prénom/nom
     * et l'e-mail seulement s'ils diffèrent (e-mail uniquement s'il est
     * fourni et valide). "full_name" est resynchronisé automatiquement par
     * le modèle Client dès que prénom + nom sont présents.
     */
    protected function findOrCreateClient(array $data): Client
    {
        $client = Client::query()->where('phone', $data['phone'])->first();
        $validEmail = $this->validEmail($data['email'] ?? null);

        if ($client) {
            $updates = array_filter([
                'first_name' => $data['first_name'] !== $client->first_name ? $data['first_name'] : null,
                'last_name' => $data['last_name'] !== $client->last_name ? $data['last_name'] : null,
                'email' => $validEmail && $validEmail !== $client->email ? $validEmail : null,
            ], fn ($value) => $value !== null);

            if ($updates !== []) {
                $client->update($updates);
            }

            return $client;
        }

        return Client::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $validEmail,
        ]);
    }

    protected function validEmail(?string $email): ?string
    {
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    protected function lockKey(Carbon $date): string
    {
        return 'appointments:lock:'.$date->toDateString();
    }
}
