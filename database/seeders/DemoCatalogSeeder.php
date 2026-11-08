<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Catalogue de DÉMONSTRATION (environnement local uniquement — jamais en production).
 * - Catégories : exemples du CDC §9.3.
 * - Produits : marqués « [DEMO] », SANS prix (« Prezzo su richiesta »), INACTIFS
 *   car ils n'ont pas encore de photo (R-PROD-02). Les photos de démonstration arrivent au sprint 4/5.
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Legna di faggio', 'Legna di quercia', 'Legna mista', 'Legna stagionata'];

        foreach ($categories as $order => $name) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => $order],
            );

            $productName = '[DEMO] '.$name;

            Product::firstOrCreate(
                ['slug' => Str::slug('demo '.$name)],
                [
                    'category_id' => $category->id,
                    'name' => $productName,
                    'short_description' => '[DEMO] Prodotto di esempio da sostituire con un prodotto reale.',
                    'description' => '<p>[DEMO] Descrizione di esempio. Sostituire con la descrizione reale del prodotto.</p>',
                    'price' => null,
                    'sale_unit_id' => null,
                    'is_active' => false,
                    'is_featured' => $order < 4,
                    'sort_order' => $order,
                ],
            );
        }
    }
}
