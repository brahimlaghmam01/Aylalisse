<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dates et nombres en français pour tout l'affichage.
        Carbon::setLocale('fr');
        CarbonImmutable::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'French');

        $this->configureRateLimiting();

        // Toutes les URLs générées (assets, redirections, liens des e-mails)
        // sont forcées en HTTPS en production. N'affecte jamais le
        // développement local (toujours servi en HTTP).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Limiteurs de débit pour les points sensibles de la réservation.
     * Le calendrier/les créneaux sont interrogés fréquemment en navigation
     * normale ; la création de rendez-vous doit rester rare par visiteur.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('booking-availability', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('booking-submit', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });

        // Connexion admin : throttlée par IP en plus du throttling par
        // e-mail+IP déjà appliqué dans AdminLoginRequest (protection
        // brute force à deux niveaux).
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
