<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\Page;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Database\Seeders\FaqSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

// F-AFAQ-01, F-ALEG-01, F-ASET-01 — sprint 8
class ContentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
        $this->seed([SettingSeeder::class, LegalPageSeeder::class, FaqSeeder::class]);
        $this->actingAs(User::factory()->create());
    }

    public function test_les_pages_admin_s_affichent(): void
    {
        $faq = Faq::first();
        $page = Page::first();

        $this->get('/admin/faq')->assertOk()->assertInertia(fn ($p) => $p->component('admin/faq/index')->has('faqs', 7));
        $this->get("/admin/faq/{$faq->id}/modifica")->assertOk()->assertInertia(fn ($p) => $p->component('admin/faq/form'));
        $this->get('/admin/pagine-legali')->assertOk()->assertInertia(fn ($p) => $p->component('admin/legal/index')->has('pages', 5));
        $this->get("/admin/pagine-legali/{$page->id}/modifica")->assertOk()->assertInertia(fn ($p) => $p->component('admin/legal/form'));
    }

    public function test_creer_une_faq_nettoie_le_html(): void
    {
        $this->post('/admin/faq', [
            'question' => 'Vendete legna di olivo?',
            'answer' => '<p>No.</p><script>x</script>',
            'topic' => 'Prodotti',
            'is_active' => '1',
        ])->assertRedirect('/admin/faq');

        $this->assertSame('<p>No.</p>', Faq::where('question', 'Vendete legna di olivo?')->value('answer'));
    }

    public function test_une_reponse_provisoire_ne_peut_pas_etre_publiee(): void
    {
        $placeholder = Faq::where('answer', 'like', '%[DA COMPLETARE]%')->first();

        $this->put("/admin/faq/{$placeholder->id}", [
            'question' => $placeholder->question,
            'answer' => $placeholder->answer,
            'is_active' => '1',
        ])->assertSessionHasErrors('answer');
    }

    public function test_reordonner_les_faq(): void
    {
        $ids = Faq::ordered()->pluck('id')->reverse()->values()->all();

        $this->post('/admin/faq/ordine', ['ids' => $ids])->assertSessionHasNoErrors();

        $this->assertSame($ids, Faq::ordered()->pluck('id')->all());
    }

    public function test_page_legale_vide_non_publiable_et_date_de_mise_a_jour(): void
    {
        $page = Page::where('slug', 'cookie-policy')->first();

        $this->put("/admin/pagine-legali/{$page->id}", ['title' => 'Cookie Policy', 'content' => '<p></p>', 'is_published' => true])
            ->assertSessionHasErrors('is_published');

        $this->put("/admin/pagine-legali/{$page->id}", ['title' => 'Cookie Policy', 'content' => '<p>Usiamo solo cookie tecnici.</p>', 'is_published' => true])
            ->assertSessionHasNoErrors();

        $page->refresh();
        $this->assertTrue($page->is_published);
        $this->assertNotNull($page->content_updated_at);

        // Le pied de page montre maintenant la page
        $this->get('/')->assertInertia(fn ($p) => $p->where('site.legal_pages.0.url', '/cookie-policy'));
    }

    public function test_donnees_entreprise_et_partita_iva(): void
    {
        $this->put('/admin/impostazioni/azienda', ['company_vat' => '12345678901'])->assertSessionHasErrors('company_vat');
        $this->put('/admin/impostazioni/azienda', ['company_tax_code' => 'ABC'])->assertSessionHasErrors('company_tax_code');

        $this->put('/admin/impostazioni/azienda', [
            'company_name' => 'Legna Premium Italia Srl',
            'company_vat' => 'IT 12345678903',
        ])->assertSessionHasNoErrors();

        $this->assertSame('12345678903', app(SettingsService::class)->get('company_vat'));
        $this->get('/')->assertInertia(fn ($p) => $p->where('site.company.vat', '12345678903'));
    }
}
