<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Normalisation commune des champs tarifaires du formulaire prestation
 * (création + édition) : accepte « 90 », « 90,00 » ou « 90.00 » et convertit
 * une valeur vide en null (permet de retirer un tarif par longueur).
 */
final class LissageServicePricingInput
{
    private const NUMERIC_FIELDS = ['price', 'price_courts', 'price_mi_longs', 'price_longs', 'deposit_amount'];

    /**
     * @return array<string, float|null>
     */
    public static function normalize(Request $request): array
    {
        $normalized = [];

        foreach (self::NUMERIC_FIELDS as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $value = $request->input($field);

            if ($value === null || $value === '') {
                $normalized[$field] = null;

                continue;
            }

            $normalized[$field] = str_replace([' ', ','], ['', '.'], (string) $value);
        }

        return $normalized;
    }
}
