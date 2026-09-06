<?php

namespace App\Support;

/**
 * Formatage monétaire français unique pour tout le projet (admin + public).
 */
final class Money
{
    /**
     * « 1 920 € » (montant entier) ou « 49,50 € » (avec centimes).
     */
    public static function eur(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;

        return number_format($amount, $decimals, ',', ' ').'&nbsp;€';
    }

    /**
     * Toujours deux décimales — pour les tableaux financiers où l'alignement
     * des centimes compte (« 190,00 € »).
     */
    public static function eurPrecise(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').'&nbsp;€';
    }
}
