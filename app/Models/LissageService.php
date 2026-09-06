<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LissageService extends Model
{
    use HasFactory;

    /**
     * Longueurs de cheveux reconnues pour la tarification par longueur.
     * Doit rester aligné avec StoreAppointmentRequest::HAIR_LENGTHS.
     */
    public const LENGTH_PRICE_COLUMNS = [
        'courts' => 'price_courts',
        'mi-longs' => 'price_mi_longs',
        'longs' => 'price_longs',
    ];

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'price_courts',
        'price_mi_longs',
        'price_longs',
        'deposit_amount',
        'duration_minutes',
        'buffer_minutes',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_courts' => 'decimal:2',
            'price_mi_longs' => 'decimal:2',
            'price_longs' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $service): void {
            if (empty($service->slug)) {
                $service->slug = static::uniqueSlugFor($service->name);
            }
        });
    }

    protected static function uniqueSlugFor(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * La prestation "Sur-Mesure Diagnostic" est affichée "Sur devis"
     * plutôt qu'avec un prix fixe côté public. Une prestation tarifée par
     * longueur n'est jamais "sur devis" (elle a au moins un prix).
     */
    public function getIsOnQuoteAttribute(): bool
    {
        if ($this->hasLengthPricing()) {
            return false;
        }

        return (float) $this->price <= 0.0;
    }

    /**
     * Vrai dès qu'au moins un prix par longueur est renseigné : le prix
     * facturé dépend alors de la longueur choisie à la réservation.
     */
    public function hasLengthPricing(): bool
    {
        foreach (self::LENGTH_PRICE_COLUMNS as $column) {
            if ($this->{$column} !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prix applicable pour une longueur donnée :
     *   - prix de la longueur s'il est renseigné ;
     *   - sinon, prix forfaitaire "price" (repli).
     * Toujours un float >= 0.
     */
    public function priceForLength(?string $hairLength): float
    {
        $column = self::LENGTH_PRICE_COLUMNS[$hairLength] ?? null;

        if ($column !== null && $this->{$column} !== null) {
            return (float) $this->{$column};
        }

        return (float) $this->price;
    }

    /**
     * Triplet tarifaire cohérent pour une longueur donnée — source unique
     * utilisée aussi bien à la création qu'à la reprogrammation d'un
     * rendez-vous. Le solde ne peut jamais être négatif.
     *
     * @return array{price: float, deposit_amount: float, remaining_amount: float}
     */
    public function pricingFor(?string $hairLength): array
    {
        $price = $this->priceForLength($hairLength);
        $deposit = (float) $this->deposit_amount;

        return [
            'price' => $price,
            'deposit_amount' => $deposit,
            'remaining_amount' => max($price - $deposit, 0.0),
        ];
    }

    /**
     * Prix par longueur exposé au frontend de réservation (clé = slug de
     * longueur, valeur = float). Uniquement les longueurs réellement tarifées.
     *
     * @return array<string, float>
     */
    public function lengthPrices(): array
    {
        $prices = [];

        foreach (self::LENGTH_PRICE_COLUMNS as $length => $column) {
            if ($this->{$column} !== null) {
                $prices[$length] = (float) $this->{$column};
            }
        }

        return $prices;
    }

    public function getTotalDurationMinutesAttribute(): int
    {
        return $this->duration_minutes + $this->buffer_minutes;
    }

    /**
     * "3h", "3h30" — durée de la prestation (hors tampon interne).
     */
    public function durationLabel(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $rest = $this->duration_minutes % 60;

        return $rest === 0 ? "{$hours}h" : "{$hours}h".str_pad((string) $rest, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Libellé de prix pour l'affichage public : « Sur devis »,
     * « À partir de 80 € » (tarif par longueur) ou « 150 € » (forfait).
     */
    public function publicPriceLabel(): string
    {
        if ($this->is_on_quote) {
            return 'Sur devis';
        }

        $lengthPrices = $this->lengthPrices();
        $amount = $lengthPrices !== [] ? min($lengthPrices) : (float) $this->price;
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;
        $formatted = number_format($amount, $decimals, ',', ' ').' €';

        return $lengthPrices !== [] ? 'À partir de '.$formatted : $formatted;
    }

    /**
     * URL publique de l'illustration de la prestation, résolue explicitement
     * sur le disque "public". Null si aucune image n'a été associée.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
