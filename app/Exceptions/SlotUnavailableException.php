<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Levée lorsqu'un créneau demandé n'est plus disponible au moment de la
 * création du rendez-vous (conflit détecté côté serveur, y compris en cas
 * de double tentative de réservation simultanée).
 */
class SlotUnavailableException extends RuntimeException
{
    public function __construct(?string $message = null)
    {
        parent::__construct(
            $message ?? "Ce créneau horaire n'est plus disponible. Veuillez en choisir un autre."
        );
    }
}
