<?php

namespace Tests\Feature\Public;

use App\Models\ContentSection;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// F-HOME v1, F-LAY-08 : accueil et pages d'erreur (sprint 3) — remplace PublicHomeTest du sprint 0
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class]);
    }

    public function test_l_accueil_affiche_les_textes_du_cdc(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lang="it"', false)
            ->assertInertia(fn ($page) => $page
                ->component('public/home')
                ->where('sections.hero.title', 'Legna da ardere di qualità per il tuo comfort quotidiano')
                ->where('sections.hero.image', null) // aucune photo inventée
                ->has('sections.perche_sceglierci.items', 4)
                ->where('translations.nav.products', 'Prodotti'));
    }

    public function test_une_section_masquee_n_est_pas_envoyee(): void
    {
        ContentSection::where('page', 'home')->where('key', 'presentazione')->update(['is_visible' => false]);

        $this->get('/')->assertInertia(fn ($page) => $page->missing('sections.presentazione'));
    }

    public function test_un_bloc_perche_sceglierci_desactive_est_retire(): void
    {
        $section = ContentSection::where('page', 'home')->where('key', 'perche_sceglierci')->first();
        $content = $section->content;
        $content['items'][1]['visible'] = false;
        $section->update(['content' => $content]);

        $this->get('/')->assertInertia(fn ($page) => $page->has('sections.perche_sceglierci.items', 3));
    }

    public function test_page_404_en_italien(): void
    {
        $this->get('/pagina-inesistente')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page
                ->component('public/error')
                ->where('status', 404)
                ->where('translations.errors.404_title', 'Pagina non trovata')
                ->has('site.contacts'));
    }

    public function test_une_404_de_l_admin_garde_la_page_standard(): void
    {
        $this->get('/admin/inesistente')->assertNotFound();
        $this->assertStringNotContainsString('public/error', $this->get('/admin/inesistente')->getContent());
    }

    public function test_les_liens_du_menu_ne_sont_pas_morts(): void
    {
        foreach (['/prodotti', '/chi-siamo', '/consegna', '/contatti', '/faq'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('public/placeholder'));
        }
    }

    public function test_aucun_panier_ni_paiement(): void
    {
        // CDC §5 / §28 : le site ne vend pas
        foreach (['/carrello', '/cart', '/checkout', '/pagamento'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }
}
