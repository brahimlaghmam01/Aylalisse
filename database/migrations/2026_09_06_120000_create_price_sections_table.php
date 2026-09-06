<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grille tarifaire entièrement administrable : une "section" regroupe des
 * lignes de tarif sous un intitulé (ex. « LISSAGE INDIEN / BRÉSILIEN »).
 * Le titre est nullable : une section sans titre sert à présenter des
 * lignes isolées (frais de déplacement, forfait Botox…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_sections');
    }
};
