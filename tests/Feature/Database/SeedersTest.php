<?php

namespace Tests\Feature\Database;

use App\Models\ContentSection;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Product;
use App\Models\SaleUnit;
use App\Models\User;
use App\Services\Content\ContentSectionRegistry;
use App\Services\Settings\SettingsService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'legna.admin.email' => 'admin@example.test',
            'legna.admin.password' => 'password-di-test-123',
        ]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoCatalogSeeder::class); // en test, l'environnement n'est pas « local »
    }

    public function test_un_administrateur_est_cree_depuis_la_configuration(): void
    {
        $this->assertTrue(User::where('email', 'admin@example.test')->where('is_active', true)->exists());
    }

    public function test_aucune_donnee_commerciale_n_est_inventee(): void
    {
        $settings = app(SettingsService::class);

        foreach (['order_email', 'contact_email', 'whatsapp_number', 'company_name', 'company_vat', 'company_address'] as $key) {
            $this->assertFalse($settings->filled($key), "Paramètre inventé : {$key}");
        }

        $this->assertSame(0, Product::whereNotNull('price')->count(), 'Aucun prix ne doit être inventé');
        $this->assertSame(0, Product::where('is_active', true)->count(), 'Les produits de démo restent inactifs');
        $this->assertSame(0, Product::where('name', 'not like', '[DEMO]%')->count(), 'Tous les produits sont marqués [DEMO]');
    }

    public function test_les_pages_legales_sont_vides_et_non_publiees(): void
    {
        $this->assertSame(5, Page::count());
        $this->assertSame(0, Page::published()->count());
    }

    public function test_toutes_les_sections_du_registre_existent(): void
    {
        $expected = collect(app(ContentSectionRegistry::class)->all())
            ->map(fn (array $sections) => count($sections))
            ->sum();

        $this->assertSame($expected, ContentSection::count());

        $hero = ContentSection::where('page', 'home')->where('key', 'hero')->first();
        $this->assertSame('Legna da ardere di qualità per il tuo comfort quotidiano', $hero->content['title']);
    }

    public function test_unites_et_faq(): void
    {
        $this->assertSame(6, SaleUnit::count());
        $this->assertSame(7, Faq::count());
        $this->assertSame(1, Faq::active()->count()); // seule « Come posso ordinare? » est complète
    }

    public function test_relancer_les_seeders_n_ecrase_pas_les_modifications(): void
    {
        app(SettingsService::class)->set('order_email', 'ordini@example.test');

        $this->seed(DatabaseSeeder::class);

        $this->assertSame('ordini@example.test', app(SettingsService::class)->get('order_email'));
        $this->assertSame(7, Faq::count());
    }
}
