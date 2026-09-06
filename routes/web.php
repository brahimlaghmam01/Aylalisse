<?php

use App\Http\Controllers\BookingAvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
| Réservation — parcours complet en 5 étapes (Alpine.js), branché sur
| AppointmentAvailabilityService / AppointmentService. Le nom de route
| "booking" (page d'accueil du parcours) est conservé tel quel : c'est
| celui utilisé par le CTA "Prendre rendez-vous" partout sur le site.
*/
Route::get('/reservation', [BookingController::class, 'index'])->name('booking');

Route::post('/reservation', [BookingController::class, 'store'])
    ->middleware('throttle:booking-submit')
    ->name('booking.store');

Route::get('/reservation/confirmation/{reference}', [BookingController::class, 'confirmation'])
    ->name('booking.confirmation');

Route::get('/reservation/availability/dates', [BookingAvailabilityController::class, 'dates'])
    ->middleware('throttle:booking-availability')
    ->name('booking.availability.dates');

Route::get('/reservation/availability/slots', [BookingAvailabilityController::class, 'slots'])
    ->middleware('throttle:booking-availability')
    ->name('booking.availability.slots');

/*
| Pages légales
*/
Route::get('/mentions-legales', [PageController::class, 'mentions'])->name('legal.mentions');
Route::get('/politique-de-confidentialite', [PageController::class, 'privacy'])->name('legal.privacy');
Route::get('/conditions-de-reservation', [PageController::class, 'terms'])->name('legal.terms');

/*
| SEO
*/
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

require __DIR__.'/admin.php';

/*
| Outils de développement local — jamais chargés en dehors de l'environnement
| "local", quelle que soit la configuration du serveur (voir routes/dev.php).
*/
if (app()->environment('local')) {
    require __DIR__.'/dev.php';
}
