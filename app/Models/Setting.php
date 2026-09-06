<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Paramètres clé/valeur pilotant la réservation (intervalle des créneaux,
 * délai minimum, horizon maximum, tampon par défaut...).
 *
 * Utilisation :
 *   Setting::get('booking_interval', 30);
 *   Setting::set('booking_interval', 30, 'integer');
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * Récupère une valeur de paramètre, typée selon la colonne "type".
     *
     * Retombe sur $default si la table n'existe pas encore (site fraîchement
     * déployé avant migration) plutôt que de faire échouer tout l'affichage
     * public — Setting::get() est appelée depuis des vues partagées comme le
     * pied de page, qui ne doivent jamais dépendre de l'état des migrations.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::query()->where('key', $key)->first();
        } catch (QueryException) {
            return $default;
        }

        if (! $setting || $setting->value === null) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Crée ou met à jour un paramètre.
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => static::prepareValue($value, $type), 'type' => $type]
        );
    }

    /**
     * Variante mise en cache de get(), réservée aux paramètres d'affichage
     * pur (coordonnées, réseaux sociaux...) qui changent rarement. Ne
     * JAMAIS l'utiliser pour les réglages consultés par
     * AppointmentAvailabilityService (booking_interval, etc.) : ceux-ci
     * doivent toujours refléter la valeur actuelle en base, sans latence
     * de cache. Le cache est explicitement invalidé par forgetCached()
     * après chaque écriture depuis /admin/parametres.
     */
    public static function getCached(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", fn () => static::get($key, $default));
    }

    public static function forgetCached(string $key): void
    {
        Cache::forget("setting:{$key}");
    }

    protected static function castValue(string $value, ?string $type): mixed
    {
        return match ($type) {
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'float', 'decimal' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    protected static function prepareValue(mixed $value, string $type): ?string
    {
        // Une valeur nulle est stockée telle quelle (colonne "value" nullable) :
        // Setting::get() retombera proprement sur son défaut. Indispensable pour
        // les réglages effaçables (sous-texte du bandeau, image Hero retirée…).
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'json', 'array' => json_encode($value),
            'bool', 'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
