<?php

namespace Tests\Feature\Public;

use App\Models\Product;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// R-STAT-03/04 — sprint 6 (Laravel 13 : le middleware CSRF s'appelle ValidateCsrfToken)
class StatsClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_clic_est_compte_par_jour(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $product = Product::factory()->active()->create();

        $this->postJson('/statistiche/clic', ['product_id' => $product->id, 'type' => 'order'])->assertNoContent();
        $this->postJson('/statistiche/clic', ['product_id' => $product->id, 'type' => 'order'])->assertNoContent();
        $this->postJson('/statistiche/clic', ['product_id' => $product->id, 'type' => 'whatsapp'])->assertNoContent();

        $this->assertSame(1, DB::table('product_daily_stats')->count()); // une seule ligne par jour
        $row = DB::table('product_daily_stats')->where('product_id', $product->id)->first();
        $this->assertSame(2, (int) $row->order_clicks);
        $this->assertSame(1, (int) $row->whatsapp_clicks);
    }

    public function test_type_ou_produit_invalide_refuse(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $hidden = Product::factory()->create(); // non publié
        $visible = Product::factory()->active()->create();

        $this->postJson('/statistiche/clic', ['product_id' => $hidden->id, 'type' => 'order'])->assertUnprocessable();
        $this->postJson('/statistiche/clic', ['product_id' => 999, 'type' => 'order'])->assertUnprocessable();
        $this->postJson('/statistiche/clic', ['product_id' => $visible->id, 'type' => 'views'])->assertUnprocessable();
    }

    public function test_les_robots_ne_sont_pas_comptes(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $product = Product::factory()->active()->create();

        $this->withHeader('User-Agent', 'Googlebot/2.1')
            ->postJson('/statistiche/clic', ['product_id' => $product->id, 'type' => 'order'])
            ->assertNoContent();

        $this->assertSame(0, DB::table('product_daily_stats')->count());
    }

    public function test_aucune_donnee_personnelle_stockee(): void
    {
        $columns = Schema::getColumnListing('product_daily_stats');

        foreach (['ip', 'ip_address', 'user_agent', 'session_id'] as $forbidden) {
            $this->assertNotContains($forbidden, $columns);
        }
    }
}
