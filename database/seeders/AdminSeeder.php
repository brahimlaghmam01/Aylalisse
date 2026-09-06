<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

/**
 * Compte administrateur de développement.
 *
 * Les identifiants peuvent être surchargés via les variables d'environnement
 * ADMIN_EMAIL / ADMIN_PASSWORD (.env). Aucun identifiant de production ne
 * doit jamais être codé en dur ni committé.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('aylalisse.admin.email');
        $password = config('aylalisse.admin.password');

        Admin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administration AylaLisse',
                'password' => $password, // haché automatiquement (cast 'hashed')
            ]
        );

        $this->command?->warn(
            "Compte admin de développement prêt — e-mail : {$email} / mot de passe : {$password} (à changer avant toute mise en production)."
        );
    }
}
