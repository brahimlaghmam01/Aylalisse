<?php

namespace Database\Seeders;

use App\Models\PriceSection;
use Illuminate\Database\Seeder;

/**
 * Grille tarifaire de départ, reprenant la structure demandée. Toutes ces
 * valeurs sont ensuite modifiables depuis /admin/tarifs — rien n'est codé
 * en dur dans les vues.
 *
 * Idempotent : on ne réinsère pas si des sections existent déjà (ne jamais
 * écraser une grille ajustée par l'administratrice).
 */
class PriceSeeder extends Seeder
{
    public function run(): void
    {
        if (PriceSection::query()->exists()) {
            return;
        }

        $sections = [
            [
                'title' => 'Lissage indien / brésilien',
                'subtitle' => null,
                'sort_order' => 1,
                'rows' => [
                    ['label' => 'Cheveux courts', 'price' => 80],
                    ['label' => 'Cheveux mi-longs', 'price' => 90],
                    ['label' => 'Cheveux longs', 'price' => 100],
                ],
            ],
            [
                'title' => null,
                'subtitle' => null,
                'sort_order' => 2,
                'rows' => [
                    ['label' => 'Frais de déplacement', 'price' => 15],
                    ['label' => 'Lissage + soin Botox', 'price' => 120],
                ],
            ],
        ];

        foreach ($sections as $section) {
            $rows = $section['rows'];
            unset($section['rows']);

            $model = PriceSection::create([...$section, 'is_active' => true]);

            foreach ($rows as $index => $row) {
                $model->rows()->create([
                    ...$row,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
