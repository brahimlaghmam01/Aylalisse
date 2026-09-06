<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Horaires d'ouverture hebdomadaires.
 * day_of_week : 0 = lundi ... 6 = dimanche.
 */
class BusinessHour extends Model
{
    use HasFactory;

    public const DAYS = [
        0 => 'Lundi',
        1 => 'Mardi',
        2 => 'Mercredi',
        3 => 'Jeudi',
        4 => 'Vendredi',
        5 => 'Samedi',
        6 => 'Dimanche',
    ];

    protected $fillable = [
        'day_of_week',
        'is_open',
        'open_time',
        'close_time',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $hour): void {
            if ($hour->is_open && $hour->open_time && $hour->close_time && $hour->close_time <= $hour->open_time) {
                throw new InvalidArgumentException("L'heure de fermeture doit être postérieure à l'heure d'ouverture.");
            }
        });
    }

    public function label(): string
    {
        return self::DAYS[$this->day_of_week] ?? (string) $this->day_of_week;
    }
}
