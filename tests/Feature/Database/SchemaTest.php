<?php

namespace Tests\Feature\Database;

use App\Enums\Availability;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_onze_tables_existent(): void
    {
        foreach ([
            'users', 'sale_units', 'categories', 'products', 'product_images', 'content_sections',
            'pages', 'faqs', 'settings', 'contact_messages', 'product_daily_stats',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table manquante : {$table}");
        }
    }

    public function test_aucune_table_de_commande_ou_de_paiement(): void
    {
        // CDC §28 : le site ne vend pas
        foreach (['orders', 'carts', 'cart_items', 'payments', 'customers'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Table interdite : {$table}");
        }
    }

    public function test_un_prix_sans_unite_est_refuse_par_la_base(): void
    {
        // R-PRIX-05 (contrainte CHECK MySQL)
        $this->expectException(QueryException::class);

        Product::factory()->create(['price' => '100.00', 'sale_unit_id' => null]);
    }

    public function test_le_prix_reste_une_chaine_et_la_disponibilite_un_enum(): void
    {
        $product = Product::factory()->withPrice('129.50')->create()->fresh();

        $this->assertSame('129.50', $product->price);
        $this->assertSame(Availability::Available, $product->availability);
        $this->assertFalse($product->isPriceOnRequest());
    }

    public function test_supprimer_une_categorie_avec_produits_est_refuse(): void
    {
        // R-CAT-04 (ON DELETE RESTRICT)
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $this->expectException(QueryException::class);

        $category->delete();
    }

    public function test_les_photos_sont_supprimees_avec_le_produit(): void
    {
        $product = Product::factory()->create();
        ProductImage::factory()->count(2)->for($product)->create();

        $product->delete();

        $this->assertSame(0, ProductImage::count());
    }
}
