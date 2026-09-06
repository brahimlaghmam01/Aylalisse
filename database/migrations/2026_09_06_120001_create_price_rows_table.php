<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une ligne de tarif : un intitulé (« Cheveux courts ») et un prix. Le prix
 * est nullable pour permettre une mention libre (« sur devis ») portée par
 * la colonne "note".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_section_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('price', 8, 2)->nullable();
            $table->string('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['price_section_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_rows');
    }
};
