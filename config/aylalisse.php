<?php

/*
|--------------------------------------------------------------------------
| Paramètres de marque AylaLisse
|--------------------------------------------------------------------------
| Valeurs par défaut utilisées tant que le panneau d'administration
| (table "settings") n'est pas encore alimenté. En Phase 2, un helper
| lira ces clés depuis la base et retombera sur ces valeurs.
*/

return [
    'brand' => 'AylaLisse',
    'tagline' => 'Spécialiste du lissage des cheveux',
    'baseline' => 'Haute coiffure • Lissage d’exception',

    'phone' => '+33 1 42 00 00 00',
    'whatsapp' => '+33 6 00 00 00 00',
    'email' => 'contact@aylalisse.fr',
    'instagram' => 'https://instagram.com/aylalisse',

    'address' => [
        'line' => '18 rue du Faubourg Saint-Honoré',
        'zip' => '75008',
        'city' => 'Paris',
    ],

    'hours' => [
        ['jour' => 'Mardi – Vendredi', 'creneau' => '10h00 – 19h00'],
        ['jour' => 'Samedi',           'creneau' => '09h30 – 18h00'],
        ['jour' => 'Dimanche – Lundi',  'creneau' => 'Fermé'],
    ],

    // Compte admin de développement (AdminSeeder). À changer avant toute mise
    // en production — ne jamais committer de vrais identifiants ici.
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@aylalisse.fr'),
        'password' => env('ADMIN_PASSWORD', 'aylalisse-dev-2026'),
    ],

    'booking' => [
        'deposit_default' => 50,   // acompte de réservation (€)
        'interval_minutes' => 30,   // pas des créneaux
        'min_notice_hours' => 24,   // délai minimum avant réservation
        'max_days_ahead' => 90,   // horizon de réservation
        'buffer_minutes' => 30,   // tampon entre deux prestations
    ],
];
