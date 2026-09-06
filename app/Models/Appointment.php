<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'client_id',
        'lissage_service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'price',
        'deposit_amount',
        'remaining_amount',
        'hair_length',
        'natural_texture',
        'chemical_history',
        'hair_notes',
        'admin_notes',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'cancelled_at' => 'datetime',
            'price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'chemical_history' => 'array',
            'status' => AppointmentStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lissageService(): BelongsTo
    {
        return $this->belongsTo(LissageService::class);
    }

    /**
     * Rendez-vous qui occupent réellement un créneau (pending, confirmed).
     */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', array_map(
            fn (AppointmentStatus $status) => $status->value,
            AppointmentStatus::blocking()
        ));
    }

    public function scopeOnDate(Builder $query, CarbonInterface $date): Builder
    {
        return $query->whereDate('appointment_date', $date->toDateString());
    }

    /**
     * Horodatage de début du rendez-vous (date + heure combinées).
     */
    public function startsAt(): Carbon
    {
        return Carbon::parse($this->appointment_date->toDateString().' '.$this->start_time);
    }

    /**
     * Horodatage de fin réelle de la prestation (hors tampon).
     */
    public function endsAt(): Carbon
    {
        return Carbon::parse($this->appointment_date->toDateString().' '.$this->end_time);
    }

    /**
     * Fin de l'occupation du créneau, tampon de nettoyage inclus.
     */
    public function blockingEndsAt(): Carbon
    {
        return $this->endsAt()->addMinutes($this->lissageService?->buffer_minutes ?? 0);
    }

    /**
     * Génère une référence unique du type AYL-20260904-AB12CD.
     */
    public static function generateUniqueReference(?CarbonInterface $date = null): string
    {
        $date ??= now();

        do {
            $candidate = sprintf('AYL-%s-%s', $date->format('Ymd'), Str::upper(Str::random(6)));
        } while (static::where('reference', $candidate)->exists());

        return $candidate;
    }
}
