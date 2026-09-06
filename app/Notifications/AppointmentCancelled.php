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
 * Envoyée à la cliente quand son rendez-vous devient "cancelled".
 * N'invente jamais de motif : n'affiche que ce qui est réellement enregistré.
 */
class AppointmentCancelled extends Notification implements ShouldQueue
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
            ->subject('Mise à jour concernant votre rendez-vous — AylaLisse')
            ->greeting('Bonjour '.$notifiable->full_name.',')
            ->line('Votre rendez-vous a été annulé.')
            ->line('**Référence :** '.$appointment->reference)
            ->line('**Prestation :** '.$appointment->lissageService->name)
            ->line('**Date :** '.$dateLabel)
            ->line('**Heure :** '.$start.' — '.$end)
            ->action('Prendre un nouveau rendez-vous', route('booking'));
    }
}
