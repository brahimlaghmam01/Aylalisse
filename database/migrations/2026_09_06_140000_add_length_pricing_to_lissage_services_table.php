<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarification par longueur de cheveux, optionnelle et additive.
 *
 * Quand au moins une de ces colonnes est renseignée pour une prestation,
 * le prix facturé à la réservation dépend de la longueur choisie
 * (courts / mi-longs / longs). Quand elles sont toutes nulles, la
 * prestation garde son prix forfaitaire "price" — comportement inchangé
 * pour les prestations existantes et pour les rendez-vous déjà pris.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lissage_services', function (Blueprint $table) {
            $table->decimal('price_courts', 10, 2)->nullable()->after('price');
            $table->decimal('price_mi_longs', 10, 2)->nullable()->after('price_courts');
            $table->decimal('price_longs', 10, 2)->nullable()->after('price_mi_longs');
        });
    }

    public function down(): void
    {
        Schema::table('lissage_services', function (Blueprint $table) {
            $table->dropColumn(['price_courts', 'price_mi_longs', 'price_longs']);
        });
    }
};
