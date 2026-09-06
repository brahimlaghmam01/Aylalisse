<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bloc de la grille tarifaire publique. Regroupe des {@see PriceRow} sous un
 * intitulé optionnel. Entièrement piloté depuis /admin/tarifs.
 */
class PriceSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(PriceRow::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Lignes visibles sur le site public (section + ligne toutes deux actives).
     */
    public function activeRows(): HasMany
    {
        return $this->rows()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
