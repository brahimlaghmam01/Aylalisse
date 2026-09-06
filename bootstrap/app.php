<?php

use App\Exceptions\SlotUnavailableException;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.guest' => RedirectIfAdminAuthenticated::class,
        ]);

        // La seule zone authentifiée de ce projet est l'administration :
        // tout visiteur non connecté est renvoyé vers /admin/login.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        // En-têtes de sécurité (X-Frame-Options, CSP...) sur toutes les
        // réponses HTTP, publiques comme admin.
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Créneau devenu indisponible entre deux vérifications : condition
        // métier attendue (double réservation évitée avec succès), pas une
        // anomalie applicative — inutile d'encombrer les logs d'erreurs.
        $exceptions->dontReport(SlotUnavailableException::class);
    })->create();
