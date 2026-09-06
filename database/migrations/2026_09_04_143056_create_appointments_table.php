<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            // Restrict : un rendez-vous historique ne doit jamais disparaître
            // accidentellement parce qu'une cliente ou une prestation est supprimée.
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('lissage_service_id')->constrained()->restrictOnDelete();

            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('pending');

            $table->decimal('price', 10, 2);
            $table->decimal('deposit_amount', 10, 2);
            $table->decimal('remaining_amount', 10, 2);

            // Diagnostic capillaire (formulaire de réservation, étape "Profil")
            $table->string('hair_length')->nullable();
            $table->string('natural_texture')->nullable();
            $table->json('chemical_history')->nullable();
            $table->text('hair_notes')->nullable();

            $table->text('admin_notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['appointment_date', 'start_time']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
