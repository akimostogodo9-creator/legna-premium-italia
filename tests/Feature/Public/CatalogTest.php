<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SaleUnit;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// F-CAT, F-PROD — sprint 5
class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class]);
    }

    /** @param array<string, mixed> $attributes */
    private function visibleProduct(array $attributes = []): Product
    {
        $product = Product::factory()->active()->create($attributes);
        ProductImage::factory()->for($product)->main()->create(['variants' => ['400' => 'p/a-400.webp', '800' => 'p/a-800.webp']]);

        return $product;
    }

    public function test_seuls_les_produits_visibles_sont_listes(): void
    {
        $visible = $this->visibleProduct();
        Product::factory()->create();                                                  // non publié
        $this->visibleProduct(['category_id' => Category::factory()->inactive()]);     // catégorie inactive

        $this->get('/prodotti')->assertOk()->assertInertia(fn ($page) => $page
            ->component('public/catalog')
            ->has('products.data', 1)
            ->where('products.data.0.id', $visible->id)
            ->where('products.data.0.price_on_request', true)
            ->where('products.data.0.url', "/prodotti/{$visible->slug}")
            ->where('products.data.0.image.srcset', fn ($srcset) => str_contains($srcset, '400w') && str_contains($srcset, '800w'))
            ->where('resultsLabel', '1 prodotto'));
    }

    public function test_la_carte_n_envoie_que_les_champs_prevus(): void
    {
        $this->visibleProduct(['meta_title' => 'Segreto SEO']);

        $this->get('/prodotti')->assertInertia(fn ($page) => $page
            ->has('products.data.0', fn ($card) => $card
                ->hasAll(['id', 'name', 'slug', 'url', 'short_description', 'category', 'price_label', 'unit', 'price_on_request', 'availability', 'image'])
                ->missing('meta_title')
                ->missing('is_active')
                ->missing('sort_order')));
    }

    public function test_filtre_par_categorie_et_categorie_inactive_en_404(): void
    {
        $faggio = Category::factory()->create(['slug' => 'faggio']);
        $this->visibleProduct(['category_id' => $faggio->id]);
        $this->visibleProduct();
        Category::factory()->inactive()->create(['slug' => 'nascosta']);

        $this->get('/prodotti/categoria/faggio')->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('currentCategory.slug', 'faggio'));

        $this->get('/prodotti/categoria/nascosta')->assertNotFound();
    }

    public function test_tri_par_prix_avec_produits_sans_prix_a_la_fin(): void
    {
        $unit = SaleUnit::factory()->create();
        $cheap = $this->visibleProduct(['price' => '50.00', 'sale_unit_id' => $unit->id]);
        $expensive = $this->visibleProduct(['price' => '200.00', 'sale_unit_id' => $unit->id]);
        $onRequest = $this->visibleProduct(['price' => null]);

        $this->get('/prodotti?ordina=prezzo-decrescente')->assertInertia(fn ($page) => $page
            ->where('products.data.0.id', $expensive->id)
            ->where('products.data.1.id', $cheap->id)
            ->where('products.data.2.id', $onRequest->id)
            ->where('products.data.0.price_label', '€ 200,00'));

        $this->get('/prodotti?ordina=prezzo-crescente')->assertInertia(fn ($page) => $page
            ->where('products.data.0.id', $cheap->id)
            ->where('products.data.2.id', $onRequest->id));
    }

    public function test_un_tri_inconnu_revient_au_tri_par_defaut(): void
    {
        $this->visibleProduct();

        $this->get('/prodotti?ordina=DROP TABLE')->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.ordina', 'in-evidenza'));
    }

    public function test_pagination(): void
    {
        app(SettingsService::class)->set('products_per_page', 2);
        foreach (range(1, 3) as $i) {
            $this->visibleProduct();
        }

        $this->get('/prodotti?pagina=2')->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.current_page', 2)
            ->where('products.last_page', 2)
            ->where('products.prev_url', fn ($url) => str_contains($url, 'pagina=1')));
    }

    public function test_fiche_produit_et_caracteristiques_renseignees_seulement(): void
    {
        $product = $this->visibleProduct(['wood_type' => 'Faggio', 'dimensions' => null, 'packaging' => '']);

        $this->get("/prodotti/{$product->slug}")->assertOk()->assertInertia(fn ($page) => $page
            ->component('public/product-show')
            ->where('preview', false)
            ->where('product.specs', [
                ['key' => 'wood_type', 'value' => 'Faggio'],
                ['key' => 'availability', 'value' => 'Disponibile'],
            ]));
    }

    public function test_produit_non_publie_404_pour_le_public_apercu_pour_l_admin(): void
    {
        $product = Product::factory()->create();

        $this->get("/prodotti/{$product->slug}")->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get("/prodotti/{$product->slug}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('preview', true));
    }

    public function test_un_admin_desactive_n_a_pas_l_apercu(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->inactive()->create())
            ->get("/prodotti/{$product->slug}")
            ->assertNotFound();
    }

    public function test_produits_similaires_de_la_meme_categorie(): void
    {
        $category = Category::factory()->create();
        $product = $this->visibleProduct(['category_id' => $category->id]);
        $this->visibleProduct(['category_id' => $category->id]);
        $this->visibleProduct(); // autre catégorie

        $this->get("/prodotti/{$product->slug}")->assertInertia(fn ($page) => $page->has('similar', 1));
    }

    public function test_le_nom_est_dans_le_html_envoye(): void
    {
        // Les données de la page sont dans le HTML initial (lues par Google et par le rendu serveur)
        $product = $this->visibleProduct(['name' => 'Legna di faggio stagionata']);

        $this->get("/prodotti/{$product->slug}")->assertSee('Legna di faggio stagionata');
    }
}
