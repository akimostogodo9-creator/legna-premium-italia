<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SaleUnit;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// F-APROD-01→07, R-PRIX, R-PROD-02, R-IMG-01→03 — sprint 4
class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        return [
            'name' => 'Legna di faggio',
            'short_description' => 'Faggio stagionato.',
            'description' => '<p>Ottima legna.</p><script>alert(1)</script>',
            'availability' => 'available',
            ...$override,
        ];
    }

    public function test_les_pages_s_affichent(): void
    {
        $this->withoutVite();
        $product = Product::factory()->create();

        $this->get('/admin/prodotti')->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/products/index')->has('products', 1));
        $this->get('/admin/prodotti/nuovo')->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/products/form')->where('product', null));
        $this->get("/admin/prodotti/{$product->id}/modifica")->assertOk()
            ->assertInertia(fn ($page) => $page->where('product.id', $product->id));
        $this->get('/admin/prodotti/unita')->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/units/index'));
    }

    public function test_creer_un_produit_sans_prix_le_laisse_non_publie(): void
    {
        $response = $this->post('/admin/prodotti', $this->payload());

        $product = Product::firstOrFail();
        $response->assertRedirect("/admin/prodotti/{$product->id}/modifica");
        $this->assertFalse($product->is_active);
        $this->assertNull($product->price);
        $this->assertStringNotContainsString('script', $product->description);
    }

    public function test_prix_italien_converti(): void
    {
        $unit = SaleUnit::factory()->create();

        $this->post('/admin/prodotti', $this->payload(['price' => '1.234,50', 'sale_unit_id' => $unit->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame('1234.50', Product::firstOrFail()->price);
    }

    public function test_prix_sans_unite_refuse(): void
    {
        $this->post('/admin/prodotti', $this->payload(['price' => '129,00']))
            ->assertSessionHasErrors('sale_unit_id');
    }

    public function test_prix_invalide_refuse(): void
    {
        $unit = SaleUnit::factory()->create();

        $this->post('/admin/prodotti', $this->payload(['price' => 'dodici', 'sale_unit_id' => $unit->id]))
            ->assertSessionHasErrors('price');
    }

    public function test_publication_refusee_sans_photo(): void
    {
        $product = Product::factory()->create(['short_description' => 'Ok']);

        $this->patch("/admin/prodotti/{$product->id}/pubblicazione")->assertSessionHasErrors('product');
        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_envoi_de_photos_puis_publication(): void
    {
        $product = Product::factory()->create(['short_description' => 'Ok']);

        $this->post("/admin/prodotti/{$product->id}/foto", [
            'photos' => [UploadedFile::fake()->image('a.jpg', 1800, 1200), UploadedFile::fake()->image('b.png', 600, 400)],
        ])->assertSessionHasNoErrors();

        $images = $product->images()->orderBy('sort_order')->get();
        $this->assertCount(2, $images);
        $this->assertSame(1, $images->where('is_main', true)->count());
        $this->assertCount(3, $images[0]->variants);   // 400, 800, 1600
        $this->assertCount(2, $images[1]->variants);   // 400, 800 (pas d'agrandissement au-delà de 600)

        // Noms aléatoires (étape 7) : rien du nom d'origine
        $this->assertStringNotContainsString('a.jpg', $images[0]->path);
        $this->assertStringEndsWith('.webp', $images[0]->path);

        $this->patch("/admin/prodotti/{$product->id}/pubblicazione")->assertSessionHasNoErrors();
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_un_fichier_non_image_est_refuse(): void
    {
        $product = Product::factory()->create();

        $this->post("/admin/prodotti/{$product->id}/foto", [
            'photos' => [UploadedFile::fake()->create('script.php', 5, 'application/x-php')],
        ])->assertSessionHasErrors('photos.0');

        $this->assertSame(0, $product->images()->count());
    }

    public function test_une_seule_photo_principale(): void
    {
        $product = Product::factory()->create();
        $first = ProductImage::factory()->for($product)->main()->create();
        $second = ProductImage::factory()->for($product)->create();

        $this->patch("/admin/foto/{$second->id}/principale")->assertSessionHasNoErrors();

        $this->assertFalse($first->fresh()->is_main);
        $this->assertTrue($second->fresh()->is_main);
    }

    public function test_texte_alternatif_et_ordre_des_photos(): void
    {
        $product = Product::factory()->create();
        $a = ProductImage::factory()->for($product)->create(['sort_order' => 0]);
        $b = ProductImage::factory()->for($product)->create(['sort_order' => 1]);

        $this->patch("/admin/foto/{$a->id}", ['alt_text' => 'Legna di faggio impilata'])->assertSessionHasNoErrors();
        $this->post("/admin/prodotti/{$product->id}/foto/ordine", ['ids' => [$b->id, $a->id]])->assertSessionHasNoErrors();

        $this->assertSame('Legna di faggio impilata', $a->fresh()->alt_text);
        $this->assertSame([$b->id, $a->id], $product->images()->orderBy('sort_order')->pluck('id')->all());
    }

    public function test_supprimer_la_photo_principale_en_designe_une_autre(): void
    {
        $product = Product::factory()->create();
        $main = ProductImage::factory()->for($product)->main()->create(['sort_order' => 0]);
        $other = ProductImage::factory()->for($product)->create(['sort_order' => 1]);

        $this->delete("/admin/foto/{$main->id}")->assertSessionHas('success');

        $this->assertTrue($other->fresh()->is_main);
    }

    public function test_supprimer_la_derniere_photo_retire_le_produit_du_site(): void
    {
        $product = Product::factory()->active()->create(['short_description' => 'Ok']);
        $image = ProductImage::factory()->for($product)->main()->create();

        $this->delete("/admin/foto/{$image->id}")->assertSessionHas('error');

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_supprimer_un_produit_supprime_ses_fichiers(): void
    {
        $product = Product::factory()->create();
        $this->post("/admin/prodotti/{$product->id}/foto", ['photos' => [UploadedFile::fake()->image('a.jpg', 900, 600)]]);
        $files = array_values($product->images()->first()->variants);

        $this->delete("/admin/prodotti/{$product->id}")->assertRedirect('/admin/prodotti');

        $this->assertModelMissing($product);
        foreach ($files as $file) {
            Storage::disk('public')->assertMissing($file);
        }
    }

    public function test_unites_ajout_modification_et_suppression(): void
    {
        $this->post('/admin/prodotti/unita', ['name' => 'mezzo bancale'])->assertSessionHasNoErrors();
        $unit = SaleUnit::where('name', 'mezzo bancale')->firstOrFail();

        $this->patch("/admin/prodotti/unita/{$unit->id}", ['name' => 'mezzo pallet'])->assertSessionHasNoErrors();
        $this->assertSame('mezzo pallet', $unit->fresh()->name);

        $this->delete("/admin/prodotti/unita/{$unit->id}")->assertSessionHasNoErrors();
        $this->assertModelMissing($unit);
    }

    public function test_unite_utilisee_non_supprimable(): void
    {
        $unit = SaleUnit::factory()->create();
        Product::factory()->create(['price' => '10.00', 'sale_unit_id' => $unit->id]);

        $this->delete("/admin/prodotti/unita/{$unit->id}")->assertSessionHasErrors('unit');
    }

    public function test_un_invite_ne_peut_rien_modifier(): void
    {
        auth()->logout();
        $product = Product::factory()->create();

        $this->delete("/admin/prodotti/{$product->id}")->assertRedirect('/admin/accesso');
        $this->assertModelExists($product);
    }
}
