<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\Admin\ConfigurationChecker;
use App\Services\Settings\SettingsService;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// F-DASH-01→04, R-STAT-05, F-ASET-05 — sprint 10
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class, FaqSeeder::class]);
        $this->actingAs(User::factory()->create());
    }

    public function test_chiffres_du_tableau_de_bord(): void
    {
        $product = Product::factory()->active()->create(['name' => 'Legna di faggio']);
        Product::factory()->create();
        ContactMessage::factory()->count(2)->create();
        DB::table('product_daily_stats')->insert([
            ['product_id' => $product->id, 'date' => now()->toDateString(), 'views' => 10, 'order_clicks' => 3, 'whatsapp_clicks' => 1],
            ['product_id' => $product->id, 'date' => now()->subDays(20)->toDateString(), 'views' => 5, 'order_clicks' => 0, 'whatsapp_clicks' => 0],
        ]);

        $this->get('/admin')->assertInertia(fn ($page) => $page
            ->component('admin/dashboard')
            ->where('products', ['total' => 2, 'active' => 1, 'hidden' => 1])
            ->where('unread', 2)
            ->where('period7.views', 10)
            ->where('period30.views', 15)
            ->where('period7.order_clicks', 3)
            ->where('topProducts.0.name', 'Legna di faggio')
            ->where('topProducts.0.requests', 4)
            ->has('daily', 30)
            ->where('daily.29.views', 10)
            ->has('latestMessages', 2));
    }

    public function test_alertes_au_demarrage(): void
    {
        $this->seed(DemoCatalogSeeder::class);

        $alerts = collect(app(ConfigurationChecker::class)->alerts());
        $keys = $alerts->pluck('key');

        foreach (['order_email', 'company', 'legal_required', 'no_products', 'demo_products', 'products_no_photo', 'faq_placeholders'] as $expected) {
            $this->assertContains($expected, $keys, "Alerte attendue : {$expected}");
        }
        $this->assertFalse(app(ConfigurationChecker::class)->readyForLaunch());

        // Triées : d'abord les bloquantes, puis les avertissements, puis les informations
        $levels = $alerts->pluck('level')->all();
        $this->assertSame($levels, collect($levels)->sortBy(fn ($l) => ['critical' => 0, 'warning' => 1, 'info' => 2][$l])->values()->all());
        $this->assertStringStartsWith('/admin/', $alerts->first()['link']);
    }

    public function test_les_alertes_disparaissent_une_fois_complete(): void
    {
        app(SettingsService::class)->setMany([
            'order_email' => 'ordini@example.test',
            'contact_email' => 'info@example.test',
            'company_name' => 'Legna Srl',
            'company_vat' => '12345678903',
        ]);
        Page::query()->update(['content' => '<p>Testo verificato.</p>', 'is_published' => true]);
        $product = Product::factory()->active()->create();
        ProductImage::factory()->for($product)->main()->create();

        $critical = collect(app(ConfigurationChecker::class)->alerts())->where('level', 'critical');

        $this->assertCount(0, $critical);
        $this->assertTrue(app(ConfigurationChecker::class)->readyForLaunch());
    }

    public function test_aucun_vocabulaire_de_vente(): void
    {
        // R-STAT-05 : le site ne vend pas — le tableau de bord ne parle ni d'ordini ni de fatturato
        $texts = strtolower(json_encode([trans('admin.dashboard'), trans('admin.alerts')]));

        foreach (['fatturato', 'vendite', 'ordini ricevuti', 'ordini confermati'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $texts);
        }
    }

    public function test_parametres_du_catalogue(): void
    {
        $this->put('/admin/impostazioni/catalogo', [
            'show_indicative_price_notice' => false,
            'products_per_page' => 24,
            'featured_count' => 6,
            'image_max_mb' => 5,
            'view_dedup_minutes' => 60,
        ])->assertSessionHasNoErrors();

        $settings = app(SettingsService::class);
        $this->assertSame(24, $settings->get('products_per_page'));
        $this->assertFalse($settings->get('show_indicative_price_notice'));

        $this->put('/admin/impostazioni/catalogo', [
            'products_per_page' => 1, 'featured_count' => 0, 'image_max_mb' => 99, 'view_dedup_minutes' => 1,
        ])->assertSessionHasErrors(['products_per_page', 'featured_count', 'image_max_mb', 'view_dedup_minutes']);
    }
}
