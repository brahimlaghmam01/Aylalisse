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
 * Envoyée à l'adresse e-mail administrateur (Setting "brand_email") à
 * chaque nouvelle demande de rendez-vous. Routée à la demande
 * (Notification::route('mail', ...)), pas liée à un modèle Admin précis.
 */
class NewAppointmentForAdmin extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(protected Appointment $appointment)
    {
        $this->appointment->loadMissing(['client', 'lissageService']);
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
            ->subject('Nouvelle demande de rendez-vous — AylaLisse')
            ->greeting('Nouvelle demande de rendez-vous')
            ->line('**Référence :** '.$appointment->reference)
            ->line('**Cliente :** '.$appointment->client->full_name)
            ->line('**Téléphone :** '.$appointment->client->phone)
            ->line('**E-mail :** '.($appointment->client->email ?? '—'))
            ->line('**Prestation :** '.$appointment->lissageService->name)
            ->line('**Date :** '.$dateLabel)
            ->line('**Heure :** '.$start.' — '.$end)
            ->line('**Statut :** '.$appointment->status->label())
            ->action('Voir le rendez-vous', route('admin.appointments.show', $appointment));
    }
}
