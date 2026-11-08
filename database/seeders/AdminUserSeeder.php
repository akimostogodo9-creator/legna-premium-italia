<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Premier administrateur : identifiants lus dans .env via config/legna.php (jamais dans le code)
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('legna.admin.email');
        $password = config('legna.admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD assenti nel .env: nessun amministratore creato.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                // « ADMIN_NAME= » vide dans .env renvoie '' (pas la valeur par défaut) : on complète ici
                'name' => config('legna.admin.name') ?: 'Amministratore',
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
