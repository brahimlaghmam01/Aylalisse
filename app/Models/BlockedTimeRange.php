<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Plage horaire ponctuelle indisponible sur une date donnée
 * (pause produit, intervention technique, rendez-vous privé...).
 */
class BlockedTimeRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $range): void {
            if ($range->start_time && $range->end_time && $range->end_time <= $range->start_time) {
                throw new InvalidArgumentException("L'heure de fin doit être postérieure à l'heure de début.");
            }
        });
    }
}
