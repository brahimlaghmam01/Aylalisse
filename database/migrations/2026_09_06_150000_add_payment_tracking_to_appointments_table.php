<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suivi administratif des encaissements (pas un système de paiement en
 * ligne). Deux horodatages, nuls par défaut : l'administratrice coche
 * « acompte payé » / « solde payé » depuis la fiche du rendez-vous.
 *
 * Un rendez-vous "completed" ne signifie PAS que le solde a été encaissé —
 * seul balance_paid_at renseigné en atteste.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('deposit_paid_at')->nullable()->after('remaining_amount');
            $table->timestamp('balance_paid_at')->nullable()->after('deposit_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['deposit_paid_at', 'balance_paid_at']);
        });
    }
};
