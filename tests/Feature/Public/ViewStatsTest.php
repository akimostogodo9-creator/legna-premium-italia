<?php

namespace Tests\Feature\Public;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// F-STAT-01, R-STAT-01/02/04 — sprint 10
class ViewStatsTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class]);
        Cache::flush();
        // Midi : la période de 31 minutes du test ne peut pas changer de jour
        $this->travelTo(now()->setTime(12, 0));
        $this->product = Product::factory()->active()->create();
        ProductImage::factory()->for($this->product)->main()->create();
    }

    private function views(): int
    {
        return (int) DB::table('product_daily_stats')->where('product_id', $this->product->id)->sum('views');
    }

    private function visit(string $agent = 'Mozilla/5.0 (Windows NT 10.0) Chrome/130'): void
    {
        $this->withHeader('User-Agent', $agent)->get("/prodotti/{$this->product->slug}")->assertOk();
    }

    public function test_une_vue_par_visiteur_et_par_periode(): void
    {
        $this->visit();
        $this->visit();
        $this->assertSame(1, $this->views());

        $this->travel(31)->minutes();
        $this->visit();
        $this->assertSame(2, $this->views());
    }

    public function test_les_robots_ne_sont_pas_comptes(): void
    {
        $this->visit('Mozilla/5.0 (compatible; Googlebot/2.1)');
        $this->visit('WhatsApp/2.23');

        $this->assertSame(0, $this->views());
    }

    public function test_l_admin_connecte_n_est_pas_compte(): void
    {
        $this->actingAs(User::factory()->create());
        $this->visit();

        $this->assertSame(0, $this->views());
    }

    public function test_aucune_donnee_personnelle_en_base(): void
    {
        $this->visit();

        $row = (array) DB::table('product_daily_stats')->first();
        $this->assertEqualsCanonicalizing(
            ['id', 'product_id', 'date', 'views', 'order_clicks', 'whatsapp_clicks', 'created_at', 'updated_at'],
            array_keys($row),
        );
    }
}
