<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Envoyée à la cliente quand l'administration fait passer son rendez-vous
 * de "pending" à "confirmed".
 */
class AppointmentConfirmed extends Notification implements ShouldQueue
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

        $message = (new MailMessage)
            ->subject('Votre rendez-vous est confirmé — AylaLisse')
            ->greeting('Bonjour '.$notifiable->full_name.',')
            ->line('Votre rendez-vous est maintenant confirmé.')
            ->line('**Prestation :** '.$appointment->lissageService->name)
            ->line('**Date :** '.$dateLabel)
            ->line('**Horaire :** '.$start.' — '.$end);

        $addressLine = Setting::get('brand_address_line');
        $addressZip = Setting::get('brand_address_zip');
        $addressCity = Setting::get('brand_address_city');

        if ($addressLine) {
            $message->line('**Adresse du salon :** '.$addressLine.($addressZip || $addressCity ? ', '.trim($addressZip.' '.$addressCity) : ''));
        }

        return $message
            ->line('Nous avons hâte de vous accueillir chez AylaLisse.')
            ->action('Retour sur AylaLisse', route('home'));
    }
}
