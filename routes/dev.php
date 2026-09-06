<?php

use App\Http\Controllers\Dev\MailPreviewController;
use Illuminate\Support\Facades\Route;

/*
| Outils de développement local uniquement. Ce fichier n'est require()
| depuis routes/web.php QUE lorsque app()->environment('local') est vrai
| (voir la fin de routes/web.php) — il ne peut donc jamais être exposé en
| production, quelle que soit la configuration du serveur web.
*/
Route::prefix('dev/emails')->name('dev.emails.')->group(function () {
    Route::get('/', [MailPreviewController::class, 'index'])->name('index');
    Route::get('/appointment-received', [MailPreviewController::class, 'received'])->name('received');
    Route::get('/admin-new-appointment', [MailPreviewController::class, 'adminNew'])->name('admin-new');
    Route::get('/appointment-confirmed', [MailPreviewController::class, 'confirmed'])->name('confirmed');
    Route::get('/appointment-cancelled', [MailPreviewController::class, 'cancelled'])->name('cancelled');
    Route::get('/appointment-rescheduled', [MailPreviewController::class, 'rescheduled'])->name('rescheduled');
});
