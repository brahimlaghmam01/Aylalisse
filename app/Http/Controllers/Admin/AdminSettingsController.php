<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    /**
     * Clés gérées par cette page, avec leur type de stockage et une valeur
     * par défaut de repli (config/aylalisse.php, pour rester cohérent tant
     * qu'un administrateur n'a rien modifié).
     */
    private const KEYS = [
        'brand_phone' => ['type' => 'string', 'default' => 'aylalisse.phone'],
        'brand_whatsapp' => ['type' => 'string', 'default' => 'aylalisse.whatsapp'],
        'brand_email' => ['type' => 'string', 'default' => 'aylalisse.email'],
        'brand_instagram' => ['type' => 'string', 'default' => 'aylalisse.instagram'],
        'brand_address_line' => ['type' => 'string', 'default' => 'aylalisse.address.line'],
        'brand_address_zip' => ['type' => 'string', 'default' => 'aylalisse.address.zip'],
        'brand_address_city' => ['type' => 'string', 'default' => 'aylalisse.address.city'],
        'booking_interval' => ['type' => 'integer', 'default' => 'aylalisse.booking.interval_minutes'],
        'minimum_booking_notice_hours' => ['type' => 'integer', 'default' => 'aylalisse.booking.min_notice_hours'],
        'maximum_booking_days' => ['type' => 'integer', 'default' => 'aylalisse.booking.max_days_ahead'],
        'default_buffer_minutes' => ['type' => 'integer', 'default' => 'aylalisse.booking.buffer_minutes'],
    ];

    /**
     * Clés lues via Setting::getCached() ailleurs dans l'app (pied de page,
     * données structurées, bouton WhatsApp) — leur cache doit être purgé
     * après chaque enregistrement pour ne jamais servir une ancienne valeur.
     * Les réglages de réservation ne sont volontairement jamais mis en
     * cache : AppointmentAvailabilityService doit toujours lire la valeur
     * actuelle en base.
     */
    private const CACHED_KEYS = [
        'brand_phone', 'brand_whatsapp', 'brand_email', 'brand_instagram',
        'brand_address_line', 'brand_address_zip', 'brand_address_city',
    ];

    public function index(): View
    {
        $settings = [];
        foreach (self::KEYS as $key => $meta) {
            $settings[$key] = Setting::get($key, config($meta['default']));
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Les réglages de réservation sont relus sans cache par
     * AppointmentAvailabilityService : les prochains calculs utilisent la
     * nouvelle valeur dès la requête suivante. Les réglages d'affichage
     * (brand_*) sont mis en cache ailleurs (Setting::getCached()) ; leur
     * cache est explicitement purgé ici pour ne jamais servir une valeur
     * périmée après enregistrement.
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        foreach (self::KEYS as $key => $meta) {
            Setting::set($key, $request->validated($key), $meta['type']);
        }

        foreach (self::CACHED_KEYS as $key) {
            Setting::forgetCached($key);
        }

        return back()->with('success', 'Les paramètres ont été enregistrés.');
    }
}
