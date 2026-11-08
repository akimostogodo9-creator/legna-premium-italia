<?php

namespace Tests\Feature\Public;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SaleUnit;
use App\Services\Ordering\OrderLinkBuilder;
use App\Services\Settings\SettingsService;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// ★ R-ORDINA-01→09, R-WA-03 — sprint 6
class OrderButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class]);
    }

    /** @param array<string, mixed> $attributes */
    private function product(array $attributes = []): Product
    {
        $product = Product::factory()->active()->create($attributes);
        ProductImage::factory()->for($product)->main()->create();

        return $product;
    }

    public function test_sans_email_de_commande_le_bouton_est_masque(): void
    {
        // R-ORDINA-05
        $product = $this->product();

        $this->get("/prodotti/{$product->slug}")->assertInertia(fn ($page) => $page
            ->where('product.order', null)
            ->where('site.general_order', null));
    }

    public function test_message_prerempli_avec_le_nom_du_produit(): void
    {
        app(SettingsService::class)->set('order_email', 'ordini@example.test');
        $product = $this->product(['name' => 'Legna d\'ulivo & quercia']);

        $this->get("/prodotti/{$product->slug}")->assertInertia(fn ($page) => $page
            ->where('product.order.email', 'ordini@example.test')
            ->where('product.order.kind', 'order')
            ->where('product.order.label', 'Ordina')
            ->where('product.order.subject', "Richiesta di ordine - Legna d'ulivo & quercia")
            ->where('product.order.body', fn ($body) => str_contains($body, "Prodotto: Legna d'ulivo & quercia")
                && str_contains($body, 'Quantità desiderata:')));
    }

    public function test_produit_epuise_utilise_le_modele_disponibilite(): void
    {
        // R-ORDINA-09 / R-CAR-04
        app(SettingsService::class)->set('order_email', 'ordini@example.test');
        $product = $this->product(['availability' => 'out_of_stock']);

        $order = app(OrderLinkBuilder::class)->forProduct($product);

        $this->assertSame('availability', $order['kind']);
        $this->assertSame('Chiedi disponibilità', $order['label']);
        $this->assertStringStartsWith('Richiesta di disponibilità', $order['subject']);
    }

    public function test_variables_prix_et_url(): void
    {
        $unit = SaleUnit::factory()->create(['name' => 'bancale']);
        $product = $this->product(['price' => '129.00', 'sale_unit_id' => $unit->id, 'slug' => 'faggio']);

        $values = app(OrderLinkBuilder::class)->values($product);

        $this->assertSame('€ 129,00', $values['prezzo']);
        $this->assertSame('bancale', $values['unita']);
        $this->assertStringEndsWith('/prodotti/faggio', $values['url_prodotto']);
    }

    public function test_sans_prix_la_variable_prix_dit_su_richiesta(): void
    {
        $values = app(OrderLinkBuilder::class)->values($this->product(['price' => null]));

        $this->assertSame('Prezzo su richiesta', $values['prezzo']);
        $this->assertSame('', $values['unita']);
    }

    public function test_whatsapp_produit(): void
    {
        app(SettingsService::class)->set('whatsapp_number', '+39 340 123 4567');
        $product = $this->product(['name' => 'Legna mista']);

        $this->get("/prodotti/{$product->slug}")->assertInertia(fn ($page) => $page
            ->where('product.whatsapp_url', fn ($url) => str_starts_with($url, 'https://wa.me/393401234567?text=')
                && str_contains(rawurldecode($url), 'Legna mista')));
    }

    public function test_les_cartes_du_catalogue_ont_le_bouton(): void
    {
        app(SettingsService::class)->set('order_email', 'ordini@example.test');
        $this->product();

        $this->get('/prodotti')->assertInertia(fn ($page) => $page->where('products.data.0.order.kind', 'order'));
    }

    public function test_message_general_pour_l_invitation_finale(): void
    {
        app(SettingsService::class)->set('order_email', 'ordini@example.test');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.general_order.kind', 'general')
            ->where('site.general_order.email', 'ordini@example.test'));
    }
}
