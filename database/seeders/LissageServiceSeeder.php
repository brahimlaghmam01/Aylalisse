<?php

namespace Database\Seeders;

use App\Models\LissageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Les quatre expériences de lissage de la maison AylaLisse.
 * Aucune autre prestation (coupe, couleur, ongles...) ne doit être ajoutée ici.
 */
class LissageServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Lissage Signature Soyeux',
                'short_description' => 'Idéal pour cheveux ondulés à bouclés avec frisottis récalcitrants. Souplesse aérienne et hydratation profonde.',
                'price' => 240.00,
                'deposit_amount' => 50.00,
                'duration_minutes' => 210,
                'buffer_minutes' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Lissage Premium Miroir',
                'short_description' => 'Brillance intense et raideur liquide pour cheveux épais, denses ou rebelles. Effet zéro frisottis sous l’humidité.',
                'price' => 310.00,
                'deposit_amount' => 60.00,
                'duration_minutes' => 240,
                'buffer_minutes' => 30,
                'sort_order' => 2,
            ],
            [
                'name' => 'Lissage Intense & Réparation',
                'short_description' => 'Cheveux très texturés, fragilisés par des colorations ou décolorations. Reconstruction profonde de la fibre.',
                'price' => 360.00,
                'deposit_amount' => 70.00,
                'duration_minutes' => 270,
                'buffer_minutes' => 30,
                'sort_order' => 3,
            ],
            [
                // "Sur devis" : price = 0 sert de convention métier pour indiquer
                // qu'il n'y a pas de tarif fixe ; le front l'affichera "Sur devis".
                'name' => 'Lissage Sur-Mesure Diagnostic',
                'short_description' => 'Cas complexes, cheveux décolorés à blanc ou extensions. Étude microscopique et protocole ajusté au millimètre.',
                'price' => 0.00,
                'deposit_amount' => 50.00,
                'duration_minutes' => 240,
                'buffer_minutes' => 30,
                'sort_order' => 4,
            ],
        ];

        foreach ($services as $service) {
            LissageService::query()->updateOrCreate(
                ['slug' => Str::slug($service['name'])],
                [...$service, 'is_active' => true]
            );
        }
    }
}
