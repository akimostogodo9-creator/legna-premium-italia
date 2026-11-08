<?php

namespace Tests\Feature\Admin;

use App\Models\ContentSection;
use App\Models\User;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// F-ACONT-01→04 — sprint 7
class ContentSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class]);
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    private function section(string $page, string $key): ContentSection
    {
        return ContentSection::where('page', $page)->where('key', $key)->firstOrFail();
    }

    public function test_les_pages_admin_s_affichent(): void
    {
        $this->withoutVite();
        $hero = $this->section('home', 'hero');

        $this->get('/admin/contenuti')->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/content/index')->has('pages', 5));
        $this->get("/admin/contenuti/{$hero->id}")->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/content/edit')
                ->where('fields.image', 'image')
                ->where('required', ['title', 'primary_label']));
        $this->get('/admin/contenuti/identita')->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/content/identity'));
    }

    public function test_modifier_le_hero_avec_une_photo(): void
    {
        $hero = $this->section('home', 'hero');

        $this->post("/admin/contenuti/{$hero->id}", [
            'title' => 'Nuovo titolo',
            'subtitle' => 'Sottotitolo',
            'primary_label' => 'Scopri i prodotti',
            'secondary_label' => 'Contattaci',
            'image_alt' => 'Cataste di legna',
            'image' => UploadedFile::fake()->image('hero.jpg', 2400, 1600),
            'image_provisional' => '1',
        ])->assertRedirect('/admin/contenuti');

        $content = $hero->fresh()->content;
        $this->assertSame('Nuovo titolo', $content['title']);
        $this->assertStringEndsWith('-1600.webp', $content['image']);
        $this->assertTrue($content['image_provisional']);
        Storage::disk('public')->assertExists($content['image']);
    }

    public function test_la_photo_du_hero_arrive_sur_l_accueil_avec_srcset(): void
    {
        $this->withoutVite();
        $hero = $this->section('home', 'hero');

        $this->post("/admin/contenuti/{$hero->id}", [
            'title' => 'Titolo',
            'primary_label' => 'Scopri',
            'image_alt' => 'Legna impilata',
            'image' => UploadedFile::fake()->image('hero.jpg', 2000, 1200),
        ]);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('sections.hero.image.alt', 'Legna impilata')
            ->where('sections.hero.image.srcset', fn ($srcset) => str_contains($srcset, '400w')
                && str_contains($srcset, '800w') && str_contains($srcset, '1600w')));
    }

    public function test_champ_obligatoire_et_html_nettoye(): void
    {
        $presentation = $this->section('home', 'presentazione');

        $this->post("/admin/contenuti/{$presentation->id}", ['title' => ''])->assertSessionHasErrors('title');

        $this->post("/admin/contenuti/{$presentation->id}", [
            'title' => 'Chi siamo',
            'text' => '<p>Ciao</p><script>alert(1)</script>',
        ])->assertSessionHasNoErrors();

        $this->assertSame('<p>Ciao</p>', $presentation->fresh()->content['text']);
    }

    public function test_blocs_perche_sceglierci_limites_et_nettoyes(): void
    {
        $why = $this->section('home', 'perche_sceglierci');

        $this->post("/admin/contenuti/{$why->id}", [
            'title' => 'Perché sceglierci',
            'items' => [
                ['icon' => 'nature', 'title' => 'Legna locale', 'text' => 'Testo', 'visible' => '1'],
                ['icon' => 'quality', 'title' => '', 'text' => 'Ignorato perché senza titolo', 'visible' => '1'],
                ['icon' => 'warmth', 'title' => 'Calore', 'text' => null, 'visible' => '0'],
            ],
        ])->assertSessionHasNoErrors();

        $items = $why->fresh()->content['items'];
        $this->assertCount(2, $items);
        $this->assertSame('nature', $items[0]['icon']);
        $this->assertFalse($items[1]['visible']);
    }

    public function test_icone_inconnue_refusee(): void
    {
        $why = $this->section('home', 'perche_sceglierci');

        $this->post("/admin/contenuti/{$why->id}", [
            'title' => 'Perché sceglierci',
            'items' => [['icon' => 'virus', 'title' => 'X']],
        ])->assertSessionHasErrors('items.0.icon');
    }

    public function test_masquer_une_section(): void
    {
        $faq = $this->section('home', 'faq');

        $this->patch("/admin/contenuti/{$faq->id}/visibilita")->assertSessionHasNoErrors();

        $this->assertFalse($faq->fresh()->is_visible);
    }

    public function test_remplacer_une_image_supprime_l_ancienne(): void
    {
        $hero = $this->section('home', 'hero');
        $payload = ['title' => 'T', 'primary_label' => 'P'];

        $this->post("/admin/contenuti/{$hero->id}", [...$payload, 'image' => UploadedFile::fake()->image('a.jpg', 1000, 700)]);
        $first = $hero->fresh()->content['image'];

        $this->post("/admin/contenuti/{$hero->id}", [...$payload, 'image' => UploadedFile::fake()->image('b.jpg', 1000, 700)]);

        Storage::disk('public')->assertMissing($first);
    }

    public function test_retirer_une_image(): void
    {
        $hero = $this->section('home', 'hero');
        $payload = ['title' => 'T', 'primary_label' => 'P'];

        $this->post("/admin/contenuti/{$hero->id}", [...$payload, 'image' => UploadedFile::fake()->image('a.jpg', 900, 600)]);
        $path = $hero->fresh()->content['image'];

        $this->post("/admin/contenuti/{$hero->id}", [...$payload, 'remove_images' => ['image']])->assertSessionHasNoErrors();

        $this->assertNull($hero->fresh()->content['image']);
        Storage::disk('public')->assertMissing($path);
    }
}
