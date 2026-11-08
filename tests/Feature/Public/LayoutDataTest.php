<?php

namespace Tests\Feature\Public;

use App\Models\Page;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

// R-CONTENU-03, R-WA-04, R-LEG-02/03 : données publiques partagées (sprint 3)
class LayoutDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class]);
    }

    public function test_sans_coordonnees_rien_n_est_affiche(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn ($page) => $page
            ->where('site.contacts.email', null)
            ->where('site.contacts.phone', null)
            ->where('site.contacts.whatsapp_url', null)
            ->where('site.company.vat', null)
            ->where('site.legal_pages', []));
    }

    public function test_avec_coordonnees_elles_sont_partagees(): void
    {
        $settings = app(SettingsService::class);
        $settings->set('whatsapp_number', '+39 340 123 4567');
        $settings->set('contact_email', 'info@example.test');
        $settings->set('company_phone', '+39 02 1234 5678');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.contacts.email', 'info@example.test')
            ->where('site.contacts.phone_href', 'tel:+390212345678')
            ->where('site.contacts.whatsapp_url', fn ($url) => str_starts_with($url, 'https://wa.me/393401234567?text=')));
    }

    public function test_seules_les_pages_legales_publiees_apparaissent(): void
    {
        Page::where('slug', 'privacy-policy')->update(['content' => '<p>Testo</p>', 'is_published' => true]);
        Page::where('slug', 'cookie-policy')->update(['content' => null, 'is_published' => true]); // vide
        Cache::forget('legna.legal_pages');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.legal_pages', [['title' => 'Privacy Policy', 'url' => '/privacy-policy']]));
    }

    public function test_l_admin_ne_recoit_pas_les_donnees_publiques(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertInertia(fn ($page) => $page->missing('site.contacts'));
    }

    public function test_le_footer_recoit_sa_description(): void
    {
        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.description', fn ($text) => str_starts_with($text, 'Scopri la nostra selezione')));
    }
}
