<?php

namespace Tests\Feature\Public;

use App\Models\Product;
use App\Models\ProductImage;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;

/**
 * F-CAT-04 : recherche — sprint 5.
 * ⚠️ Piège MySQL : un index FULLTEXT InnoDB ne « voit » pas les lignes d'une transaction non validée.
 * RefreshDatabase travaillant dans une transaction, ce test utilise DatabaseTruncation
 * (tables de la base de TEST vidées entre les tests, sans transaction).
 */
class CatalogSearchTest extends TestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class]);
    }

    protected function tearDown(): void
    {
        // DatabaseTruncation vide les tables AVANT chaque test, pas après : on vide aussi
        // à la fin pour ne pas laisser de produits aux tests suivants (qui utilisent RefreshDatabase).
        $this->truncateTablesForAllConnections();

        parent::tearDown();
    }

    /** @param array<string, mixed> $attributes */
    private function visibleProduct(array $attributes = []): Product
    {
        $product = Product::factory()->active()->create($attributes);
        ProductImage::factory()->for($product)->main()->create();

        return $product;
    }

    public function test_recherche_avec_mots_courts_et_accents(): void
    {
        $this->visibleProduct(['name' => 'Legna di faggio', 'short_description' => 'Qualità superiore']);
        $this->visibleProduct(['name' => 'Legna di quercia', 'short_description' => 'Lunga durata']);

        $this->get('/prodotti?q=faggio')->assertInertia(fn ($page) => $page->has('products.data', 1));
        $this->get('/prodotti?q=fag')->assertInertia(fn ($page) => $page->has('products.data', 1)); // début de mot
        $this->get('/prodotti?q=qualità')->assertInertia(fn ($page) => $page->has('products.data', 1));
        $this->get('/prodotti?q=di')->assertInertia(fn ($page) => $page->has('products.data', 2)); // mot court → LIKE
        $this->get('/prodotti?q=castagno')->assertInertia(fn ($page) => $page->has('products.data', 0));
        $this->get('/prodotti?q="><script>')->assertOk(); // caractères spéciaux neutralisés
    }
}
