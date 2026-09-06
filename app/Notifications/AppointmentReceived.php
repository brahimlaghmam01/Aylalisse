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
 * Envoyée à la cliente dès qu'une demande de rendez-vous est créée
 * (statut "pending"). Ne confirme rien : informe seulement que la demande
 * a bien été reçue et sera examinée.
 */
class AppointmentReceived extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(protected Appointment $appointment)
    {
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
        $dateLabel = Str::ucfirst($appointment->appointment_date->translatedFormat('l j F Y'));
        $start = Carbon::parse($appointment->start_time)->format('H:i');
        $end = Carbon::parse($appointment->end_time)->format('H:i');

        return (new MailMessage)
            ->subject('Nous avons bien reçu votre demande — AylaLisse')
            ->greeting('Bonjour '.$notifiable->full_name.',')
            ->line('Merci d’avoir choisi AylaLisse.')
            ->line('Votre demande de rendez-vous a bien été enregistrée.')
            ->line('**Référence :** '.$appointment->reference)
            ->line('**Prestation :** '.$appointment->lissageService->name)
            ->line('**Date :** '.$dateLabel)
            ->line('**Horaire :** '.$start.' — '.$end)
            ->line('**Statut :** Demande en attente de confirmation')
            ->line('Votre rendez-vous n’est pas encore définitivement confirmé.')
            ->line('Nous vous contacterons prochainement pour confirmer votre créneau.')
            ->action('Retour sur AylaLisse', route('home'));
    }
}
