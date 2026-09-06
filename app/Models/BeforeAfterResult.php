<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BeforeAfterResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'before_image',
        'after_image',
        'hair_type',
        'lissage_type',
        'description',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
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
     * URL publique de l'image "avant". Toujours résolue sur le disque
     * "public" explicitement — indépendamment de FILESYSTEM_DISK — pour que
     * l'affichage ne dépende jamais du disque par défaut de l'environnement.
     */
    public function getBeforeImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->before_image);
    }

    public function getAfterImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->after_image);
    }

    /**
     * Un résultat n'est affichable côté public que si ses deux fichiers
     * existent réellement sur le disque : une image manquante ne doit jamais
     * casser la page, la ligne est simplement ignorée.
     */
    public function getHasBothImagesAttribute(): bool
    {
        return $this->fileExists($this->before_image) && $this->fileExists($this->after_image);
    }

    private function imageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function fileExists(?string $path): bool
    {
        return filled($path) && Storage::disk('public')->exists($path);
    }
}
