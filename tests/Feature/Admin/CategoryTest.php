<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// F-ACAT-01/02, R-CAT-04, R-IMG-02 — sprint 4
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_la_liste_s_affiche(): void
    {
        $this->withoutVite();
        Category::factory()->count(2)->create();

        $this->get('/admin/categorie')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/categories/index')->has('categories', 2));
    }

    public function test_creer_une_categorie_avec_image(): void
    {
        $this->post('/admin/categorie', [
            'name' => 'Legna di faggio',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('faggio.jpg', 2000, 1500),
        ])->assertRedirect('/admin/categorie');

        $category = Category::firstOrFail();
        $this->assertSame('legna-di-faggio', $category->slug);
        $this->assertStringEndsWith('-1600.webp', $category->image_path);
        Storage::disk('public')->assertExists($category->image_path);
    }

    public function test_remplacer_l_image_supprime_l_ancienne(): void
    {
        $this->post('/admin/categorie', [
            'name' => 'Faggio',
            'image' => UploadedFile::fake()->image('a.jpg', 1000, 800),
        ]);
        $category = Category::firstOrFail();
        $old = $category->image_path;

        $this->post("/admin/categorie/{$category->id}", [
            'name' => 'Faggio',
            'image' => UploadedFile::fake()->image('b.jpg', 1000, 800),
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($category->fresh()->image_path);
    }

    public function test_slug_unique_automatique(): void
    {
        Category::factory()->create(['slug' => 'legna-di-faggio']);

        $this->post('/admin/categorie', ['name' => 'Legna di faggio']);

        $this->assertTrue(Category::where('slug', 'legna-di-faggio-2')->exists());
    }

    public function test_un_fichier_qui_n_est_pas_une_image_est_refuse(): void
    {
        $this->post('/admin/categorie', [
            'name' => 'Test',
            'image' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('image');
    }

    public function test_une_image_trop_lourde_est_refusee(): void
    {
        $this->post('/admin/categorie', [
            'name' => 'Test',
            'image' => UploadedFile::fake()->image('enorme.jpg')->size(9 * 1024), // 9 Mo > 8 Mo
        ])->assertSessionHasErrors('image');
    }

    public function test_supprimer_une_categorie_non_vide_est_refuse(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $this->delete("/admin/categorie/{$category->id}")->assertSessionHasErrors('category');
        $this->assertModelExists($category);
    }

    public function test_reordonner(): void
    {
        [$a, $b] = Category::factory()->count(2)->create();

        $this->post('/admin/categorie/ordine', ['ids' => [$b->id, $a->id]])->assertSessionHasNoErrors();

        $this->assertSame([$b->id, $a->id], Category::ordered()->pluck('id')->all());
    }
}
