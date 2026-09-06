<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une ligne de la grille tarifaire (« Cheveux courts — 80 € »).
 */
class PriceRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'price_section_id',
        'label',
        'price',
        'note',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(PriceSection::class, 'price_section_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Prix formaté à la française : « 80 € », « 90,50 € », ou la mention
     * libre (« sur devis ») si aucun prix n'est renseigné.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null) {
            return $this->note ?: 'Sur devis';
        }

        $price = (float) $this->price;
        $decimals = fmod($price, 1.0) === 0.0 ? 0 : 2;

        return number_format($price, $decimals, ',', ' ').' €';
    }
}
