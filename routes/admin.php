<?php

use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAvailabilityController;
use App\Http\Controllers\Admin\AdminBeforeAfterController;
use App\Http\Controllers\Admin\AdminCalendarController;
use App\Http\Controllers\Admin\AdminClientController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLissageServiceController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminTestimonialController;
use Illuminate\Support\Facades\Route;

/*
| Panneau d'administration AylaLisse — entièrement protégé par le guard
| "admin" (voir config/auth.php). Aucune de ces routes n'est accessible
| au public.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    // Plafond raisonnable sur l'ensemble de l'admin authentifiée, en plus
    // des limiteurs dédiés (booking-availability, booking-submit,
    // admin-login) : protège contre un abus/bug côté client sans gêner un
    // usage normal (dashboard, filtres, calendrier...).
    Route::middleware(['auth:admin', 'throttle:180,1'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('rendez-vous')->name('appointments.')->group(function () {
            Route::get('/', [AdminAppointmentController::class, 'index'])->name('index');
            Route::get('/{appointment}', [AdminAppointmentController::class, 'show'])->name('show');
            Route::patch('/{appointment}/statut', [AdminAppointmentController::class, 'updateStatus'])->name('status');
            Route::patch('/{appointment}/reprogrammer', [AdminAppointmentController::class, 'reschedule'])->name('reschedule');
            Route::patch('/{appointment}/notes', [AdminAppointmentController::class, 'updateNotes'])->name('notes');
        });

        Route::get('/calendrier', [AdminCalendarController::class, 'index'])->name('calendar');
        Route::get('/calendrier/evenements', [AdminCalendarController::class, 'events'])->name('calendar.events');

        Route::prefix('clientes')->name('clients.')->group(function () {
            Route::get('/', [AdminClientController::class, 'index'])->name('index');
            Route::get('/{client}', [AdminClientController::class, 'show'])->name('show');
        });

        Route::prefix('prestations')->name('services.')->group(function () {
            Route::get('/', [AdminLissageServiceController::class, 'index'])->name('index');
            Route::get('/creer', [AdminLissageServiceController::class, 'create'])->name('create');
            Route::post('/', [AdminLissageServiceController::class, 'store'])->name('store');
            Route::get('/{lissageService}/modifier', [AdminLissageServiceController::class, 'edit'])->name('edit');
            Route::put('/{lissageService}', [AdminLissageServiceController::class, 'update'])->name('update');
            Route::patch('/{lissageService}/statut', [AdminLissageServiceController::class, 'toggleActive'])->name('toggle');
        });

        Route::prefix('resultats')->name('results.')->group(function () {
            Route::get('/', [AdminBeforeAfterController::class, 'index'])->name('index');
            Route::get('/creer', [AdminBeforeAfterController::class, 'create'])->name('create');
            Route::post('/', [AdminBeforeAfterController::class, 'store'])->name('store');
            Route::get('/{result}/modifier', [AdminBeforeAfterController::class, 'edit'])->name('edit');
            Route::put('/{result}', [AdminBeforeAfterController::class, 'update'])->name('update');
            Route::patch('/{result}/statut', [AdminBeforeAfterController::class, 'togglePublished'])->name('toggle');
            Route::delete('/{result}', [AdminBeforeAfterController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('temoignages')->name('testimonials.')->group(function () {
            Route::get('/', [AdminTestimonialController::class, 'index'])->name('index');
            Route::get('/creer', [AdminTestimonialController::class, 'create'])->name('create');
            Route::post('/', [AdminTestimonialController::class, 'store'])->name('store');
            Route::get('/{testimonial}/modifier', [AdminTestimonialController::class, 'edit'])->name('edit');
            Route::put('/{testimonial}', [AdminTestimonialController::class, 'update'])->name('update');
            Route::patch('/{testimonial}/statut', [AdminTestimonialController::class, 'togglePublished'])->name('toggle');
            Route::delete('/{testimonial}', [AdminTestimonialController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('disponibilites')->name('availability.')->group(function () {
            Route::get('/', [AdminAvailabilityController::class, 'index'])->name('index');
            Route::put('/horaires/{businessHour}', [AdminAvailabilityController::class, 'updateBusinessHour'])->name('hours.update');
            Route::post('/dates-bloquees', [AdminAvailabilityController::class, 'storeBlockedDate'])->name('blocked-dates.store');
            Route::delete('/dates-bloquees/{blockedDate}', [AdminAvailabilityController::class, 'destroyBlockedDate'])->name('blocked-dates.destroy');
            Route::post('/plages-bloquees', [AdminAvailabilityController::class, 'storeBlockedTimeRange'])->name('blocked-ranges.store');
            Route::delete('/plages-bloquees/{blockedTimeRange}', [AdminAvailabilityController::class, 'destroyBlockedTimeRange'])->name('blocked-ranges.destroy');
        });

        Route::get('/parametres', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/parametres', [AdminSettingsController::class, 'update'])->name('settings.update');
    });
});
