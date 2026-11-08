<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_seuls_les_produits_actifs_de_categories_actives_sont_visibles(): void
    {
        $visible = Product::factory()->active()->create();
        Product::factory()->create(); // inactif
        Product::factory()->active()->for(Category::factory()->inactive())->create(); // catégorie inactive
        $sansCategorie = Product::factory()->active()->create(['category_id' => null]);

        $ids = Product::visible()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$visible->id, $sansCategorie->id], $ids);
    }

    public function test_photo_principale_ou_premiere_photo(): void
    {
        $product = Product::factory()->create();
        $first = ProductImage::factory()->for($product)->create(['sort_order' => 0]);
        ProductImage::factory()->for($product)->create(['sort_order' => 1]);

        $this->assertTrue($product->fresh()->mainImage()->is($first));

        $main = ProductImage::factory()->for($product)->main()->create(['sort_order' => 2]);

        $this->assertTrue($product->fresh()->mainImage()->is($main));
    }

    public function test_produit_epuise(): void
    {
        $this->assertTrue(Product::factory()->outOfStock()->make()->isOutOfStock());
        $this->assertTrue(Product::factory()->make()->isPriceOnRequest());
    }
}
