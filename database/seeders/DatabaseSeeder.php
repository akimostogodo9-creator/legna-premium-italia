<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Données de départ du site. Aucune donnée commerciale inventée (CDC §31).
     * DemoCatalogSeeder n'est appelé qu'en environnement local (jamais en production — sprint 13).
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SaleUnitSeeder::class,
            SettingSeeder::class,
            ContentSectionSeeder::class,
            LegalPageSeeder::class,
            FaqSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoCatalogSeeder::class);
        }
    }
}
