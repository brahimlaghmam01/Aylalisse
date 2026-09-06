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
        'deposit_paid_at',
        'balance_paid_at',
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
            'deposit_paid_at' => 'datetime',
            'balance_paid_at' => 'datetime',
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
     * Un rendez-vous dont le prix figé est nul correspond à une prestation
     * "sur devis" : le montant sera arrêté après le diagnostic capillaire.
     */
    public function isOnQuote(): bool
    {
        return (float) $this->price <= 0.0;
    }

    /**
     * Libellé d'affichage du prix figé — « Sur devis » plutôt que « 0,00 € ».
     */
    public function priceLabel(): string
    {
        return $this->isOnQuote()
            ? 'Sur devis'
            : number_format((float) $this->price, 2, ',', ' ').' €';
    }

    /**
     * Libellé du solde restant — « À définir après diagnostic » quand le
     * prix n'est pas encore arrêté.
     */
    public function remainingLabel(): string
    {
        return $this->isOnQuote()
            ? 'À définir après diagnostic'
            : number_format((float) $this->remaining_amount, 2, ',', ' ').' €';
    }

    public function depositLabel(): string
    {
        return number_format((float) $this->deposit_amount, 2, ',', ' ').' €';
    }

    /*
    |--------------------------------------------------------------------------
    | Encaissements (suivi administratif)
    |--------------------------------------------------------------------------
    */

    public function isDepositPaid(): bool
    {
        return $this->deposit_paid_at !== null;
    }

    public function isBalancePaid(): bool
    {
        return $this->balance_paid_at !== null;
    }

    /**
     * Une prestation est réellement réalisée (et compte dans le chiffre
     | d'affaires) uniquement au statut "completed".
     */
    public function isRevenueRealised(): bool
    {
        return $this->status === AppointmentStatus::Completed;
    }

    /**
     * Montant réellement encaissé sur ce rendez-vous = acompte s'il est
     * marqué payé + solde s'il est marqué payé. Jamais déduit du statut.
     */
    public function amountCollected(): float
    {
        $collected = 0.0;

        if ($this->isDepositPaid()) {
            $collected += (float) $this->deposit_amount;
        }

        if ($this->isBalancePaid()) {
            $collected += (float) $this->remaining_amount;
        }

        return round($collected, 2);
    }

    /**
     * Montant restant réellement à encaisser sur ce rendez-vous : ce qui
     * n'a pas encore été marqué payé. Sur devis (prix nul) => 0.
     */
    public function amountOutstanding(): float
    {
        if ($this->isOnQuote()) {
            return 0.0;
        }

        $outstanding = 0.0;

        if (! $this->isDepositPaid()) {
            $outstanding += (float) $this->deposit_amount;
        }

        if (! $this->isBalancePaid()) {
            $outstanding += (float) $this->remaining_amount;
        }

        return round($outstanding, 2);
    }

    /**
     * Rendez-vous terminés (chiffre d'affaires réalisé). Le prix utilisé est
     * toujours le prix historique figé sur la ligne.
     */
    public function scopeRealisedRevenue(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::Completed->value);
    }

    public function scopeBetweenDates(Builder $query, CarbonInterface $from, CarbonInterface $to): Builder
    {
        return $query->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()]);
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
