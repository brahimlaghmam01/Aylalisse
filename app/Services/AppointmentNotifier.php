<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Setting;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentReceived;
use App\Notifications\AppointmentRescheduled;
use App\Notifications\NewAppointmentForAdmin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Point d'entrée unique pour les notifications e-mail liées aux rendez-vous.
 *
 * Règle d'or : un problème d'envoi d'e-mail (SMTP indisponible, etc.) ne
 * doit jamais faire échouer une réservation ni une action admin. Chaque
 * méthode avale ses propres exceptions et journalise l'échec au lieu de le
 * laisser remonter — les notifications sont de toute façon mises en file
 * d'attente (ShouldQueue), donc l'envoi réel se produit dans un processus
 * séparé (queue:work), bien après que la requête HTTP a répondu.
 */
class AppointmentNotifier
{
    /**
     * Nouvelle demande de rendez-vous : notifie la cliente (si un e-mail
     * est renseigné) et l'administration (adresse configurée dans les
     * paramètres, jamais codée en dur).
     */
    public function notifyNewAppointment(Appointment $appointment): void
    {
        $this->safely(function () use ($appointment) {
            if ($appointment->client->email) {
                $appointment->client->notify(new AppointmentReceived($appointment));
            }
        });

        $this->safely(function () use ($appointment) {
            $adminEmail = Setting::get('brand_email');

            if ($adminEmail) {
                Notification::route('mail', $adminEmail)->notify(new NewAppointmentForAdmin($appointment));
            }
        });
    }

    public function notifyConfirmed(Appointment $appointment): void
    {
        $this->safely(function () use ($appointment) {
            if ($appointment->client->email) {
                $appointment->client->notify(new AppointmentConfirmed($appointment));
            }
        });
    }

    public function notifyCancelled(Appointment $appointment): void
    {
        $this->safely(function () use ($appointment) {
            if ($appointment->client->email) {
                $appointment->client->notify(new AppointmentCancelled($appointment));
            }
        });
    }

    public function notifyRescheduled(Appointment $appointment, string $previousDate, string $previousStartTime): void
    {
        $this->safely(function () use ($appointment, $previousDate, $previousStartTime) {
            if ($appointment->client->email) {
                $appointment->client->notify(new AppointmentRescheduled($appointment, $previousDate, $previousStartTime));
            }
        });
    }

    protected function safely(callable $dispatch): void
    {
        try {
            $dispatch();
        } catch (Throwable $exception) {
            Log::error('Échec de l’envoi d’une notification de rendez-vous.', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
