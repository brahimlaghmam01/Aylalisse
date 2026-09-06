<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentReceived;
use App\Notifications\AppointmentRescheduled;
use App\Notifications\NewAppointmentForAdmin;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Prévisualisation locale des e-mails de rendez-vous — jamais exposée en
 * production (voir routes/dev.php, chargé uniquement si app()->environment('local')).
 * Utilise un vrai rendez-vous existant (le plus récent par défaut, ou
 * ?appointment=ID) pour un rendu fidèle plutôt que des données inventées.
 */
class MailPreviewController extends Controller
{
    public function index(): Response
    {
        $this->guard();

        $links = collect([
            'appointment-received' => 'Cliente — Demande reçue',
            'admin-new-appointment' => 'Admin — Nouvelle demande',
            'appointment-confirmed' => 'Cliente — Rendez-vous confirmé',
            'appointment-cancelled' => 'Cliente — Rendez-vous annulé',
            'appointment-rescheduled' => 'Cliente — Rendez-vous reprogrammé',
        ])->map(fn ($label, $slug) => "<li><a href=\"/dev/emails/{$slug}\">{$label}</a></li>")->implode('');

        return response("<h1>Prévisualisation des e-mails AylaLisse</h1><ul>{$links}</ul><p>Basé sur le rendez-vous le plus récent de la base locale.</p>");
    }

    public function received(): Response
    {
        return $this->render(fn (Appointment $a) => new AppointmentReceived($a));
    }

    public function adminNew(): Response
    {
        return $this->render(fn (Appointment $a) => new NewAppointmentForAdmin($a));
    }

    public function confirmed(): Response
    {
        return $this->render(fn (Appointment $a) => new AppointmentConfirmed($a));
    }

    public function cancelled(): Response
    {
        return $this->render(fn (Appointment $a) => new AppointmentCancelled($a));
    }

    public function rescheduled(): Response
    {
        return $this->render(fn (Appointment $a) => new AppointmentRescheduled(
            $a,
            $a->appointment_date->copy()->subDay()->toDateString(),
            $a->start_time,
        ));
    }

    private function render(\Closure $makeNotification): Response
    {
        $this->guard();

        $appointment = request()->filled('appointment')
            ? Appointment::with(['client', 'lissageService'])->findOrFail((int) request('appointment'))
            : Appointment::with(['client', 'lissageService'])->latest('id')->first();

        if (! $appointment) {
            return response('Aucun rendez-vous en base locale pour générer un aperçu. Créez-en un via /reservation d’abord.', SymfonyResponse::HTTP_NOT_FOUND);
        }

        $mailMessage = $makeNotification($appointment)->toMail($appointment->client);

        return response($mailMessage->render());
    }

    private function guard(): void
    {
        abort_unless(App::environment('local'), 404);
    }
}
