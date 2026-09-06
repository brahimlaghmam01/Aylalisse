<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Envoyée à la cliente quand l'administration reprogramme son rendez-vous.
 * $previousDate / $previousStartTime doivent être capturés par l'appelant
 * AVANT la mise à jour du modèle (l'appointment reçu ici porte déjà les
 * nouvelles valeurs).
 */
class AppointmentRescheduled extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        protected Appointment $appointment,
        protected string $previousDate,
        protected string $previousStartTime,
    ) {
        $this->appointment->loadMissing('lissageService');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;

        $oldDateLabel = Str::ucfirst(Carbon::parse($this->previousDate)->translatedFormat('l j F Y'));
        $oldStart = Carbon::parse($this->previousStartTime)->format('H:i');

        $newDateLabel = Str::ucfirst($appointment->appointment_date->translatedFormat('l j F Y'));
        $newStart = Carbon::parse($appointment->start_time)->format('H:i');
        $newEnd = Carbon::parse($appointment->end_time)->format('H:i');

        return (new MailMessage)
            ->subject('Votre rendez-vous a été reprogrammé — AylaLisse')
            ->greeting('Bonjour '.$notifiable->full_name.',')
            ->line('Votre rendez-vous a été reprogrammé.')
            ->line('**Référence :** '.$appointment->reference)
            ->line('**Prestation :** '.$appointment->lissageService->name)
            ->line('**Ancienne date :** '.$oldDateLabel.' à '.$oldStart)
            ->line('**Nouvelle date :** '.$newDateLabel)
            ->line('**Nouvel horaire :** '.$newStart.' — '.$newEnd)
            ->action('Retour sur AylaLisse', route('home'));
    }
}
