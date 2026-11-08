<?php

namespace Database\Seeders;

use App\Models\SaleUnit;
use Illuminate\Database\Seeder;

// Étape 2 §3.3 : unités par défaut, modifiables par l'admin
class SaleUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = ['sacco', 'bancale', 'quintale', 'metro stero', 'pezzo', 'kg'];

        foreach ($units as $order => $name) {
            SaleUnit::updateOrCreate(['name' => $name], ['sort_order' => $order]);
        }
    }
}
