<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajout non destructif : "full_name" est conservé (clientes existantes,
     * notifications, affichages déjà en place) et reste synchronisé
     * automatiquement par le modèle Client dès que prénom + nom sont
     * renseignés. Les deux nouvelles colonnes sont nullable pour ne jamais
     * casser une ligne existante qui n'aurait que "full_name".
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
