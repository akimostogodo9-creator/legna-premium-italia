<?php

namespace Tests\Feature\Public;

use App\Models\ContentSection;
use App\Models\Faq;
use App\Models\Page;
use App\Services\Settings\SettingsService;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

// F-PAGE-01→04, R-LIV-03, R-LEG-02/03 — sprint 8
class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class, FaqSeeder::class]);
    }

    public function test_chi_siamo_n_affiche_que_les_blocs_remplis(): void
    {
        // Au départ : aucun bloc rempli (rien n'est inventé)
        $this->get('/chi-siamo')->assertOk()->assertInertia(fn ($page) => $page->component('public/about')->where('blocks', []));

        $storia = ContentSection::where('page', 'chi_siamo')->where('key', 'storia')->first();
        $storia->update(['content' => [...$storia->content, 'body' => '<p>Dal 1990…</p>']]);

        $this->get('/chi-siamo')->assertInertia(fn ($page) => $page->has('blocks', 1)->where('blocks.0.key', 'storia'));
    }

    public function test_consegna_n_affiche_que_les_blocs_remplis_et_visibles(): void
    {
        $costi = ContentSection::where('page', 'consegna')->where('key', 'costi')->first();
        $costi->update(['content' => [...$costi->content, 'body' => '<p>Costi da definire.</p>']]);

        $this->get('/consegna')->assertInertia(fn ($page) => $page->has('blocks', 1));

        $costi->update(['is_visible' => false]);

        $this->get('/consegna')->assertInertia(fn ($page) => $page->where('blocks', []));
    }

    public function test_faq_groupee_et_seulement_les_actives(): void
    {
        Faq::create(['question' => 'Fate consegne il sabato?', 'answer' => '<p>Sì.</p>', 'topic' => 'Consegna', 'is_active' => true, 'sort_order' => 99]);

        $this->get('/faq')->assertInertia(fn ($page) => $page
            ->component('public/faq')
            ->has('groups', 2)
            ->where('groups.0.topic', 'Ordini')
            ->where('groups.1.topic', 'Consegna')
            ->has('groups.1.faqs', 1));
    }

    public function test_pages_legales_vides_ou_non_publiees_en_404(): void
    {
        $this->get('/privacy-policy')->assertNotFound();

        Page::where('slug', 'privacy-policy')->update(['content' => '<p>Testo</p>', 'is_published' => true]);

        $this->get('/privacy-policy')->assertOk()->assertInertia(fn ($page) => $page->where('page.title', 'Privacy Policy'));
        $this->get('/pagina-a-caso')->assertNotFound();
    }

    public function test_informazioni_azienda_affiche_les_donnees_saisies(): void
    {
        app(SettingsService::class)->setMany(['company_name' => 'Legna Srl', 'company_vat' => '12345678903']);
        Page::where('slug', 'informazioni-azienda')->update(['content' => '<p>Chi siamo.</p>', 'is_published' => true]);

        $this->get('/informazioni-azienda')->assertInertia(fn ($page) => $page
            ->where('company', ['company_name' => 'Legna Srl', 'company_vat' => '12345678903']));
    }
}
