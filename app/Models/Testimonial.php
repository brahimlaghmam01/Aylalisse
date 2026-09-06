<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'content',
        'rating',
        'image',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Filet de sécurité au niveau du modèle : la validation métier
        // (1 à 5) est également imposée en Form Request.
        static::saving(function (self $testimonial): void {
            if ($testimonial->rating < 1 || $testimonial->rating > 5) {
                throw new InvalidArgumentException('La note d’un témoignage doit être comprise entre 1 et 5.');
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    /**
     * URL publique de la photo de la cliente, résolue explicitement sur le
     * disque "public" (jamais tributaire de FILESYSTEM_DISK). Null si aucune
     * photo n'a été fournie.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }
}
