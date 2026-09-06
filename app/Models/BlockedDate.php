<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Journée entièrement fermée à la réservation (vacances, jour férié...).
 */
class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
