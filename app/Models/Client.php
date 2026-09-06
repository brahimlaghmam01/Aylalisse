<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'phone',
        'email',
    ];

    protected static function booted(): void
    {
        // "full_name" reste la source utilisée par les notifications, le
        // pied de page admin et tout l'affichage existant : on la maintient
        // automatiquement à jour dès que prénom + nom sont renseignés,
        // plutôt que de faire dépendre chaque appelant des deux nouveaux
        // champs. Les clientes historiques qui n'auraient que "full_name"
        // (créées avant cette évolution) ne sont jamais affectées.
        static::saving(function (self $client): void {
            if ($client->first_name && $client->last_name) {
                $client->full_name = trim($client->first_name.' '.$client->last_name);
            }
        });
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
