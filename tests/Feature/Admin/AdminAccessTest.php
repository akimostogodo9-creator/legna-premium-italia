<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// R-ADM-02 / R-ADM-04 : accès à l'administration et données partagées (sprint 2)
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_un_invite_est_redirige_vers_la_connexion(): void
    {
        $this->get('/admin')->assertRedirect('/admin/accesso');
        $this->get('/admin/utenti')->assertRedirect('/admin/accesso');
    }

    public function test_un_admin_actif_accede_au_pannello(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk();
    }

    public function test_un_admin_desactive_est_deconnecte(): void
    {
        $this->actingAs(User::factory()->inactive()->create())
            ->get('/admin')
            ->assertRedirect('/admin/accesso');

        $this->assertGuest();
    }

    public function test_les_traductions_admin_sont_partagees(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertInertia(fn ($page) => $page->where('translations.nav.products', 'Prodotti'));
    }

    public function test_la_page_de_connexion_recoit_les_traductions_admin(): void
    {
        $this->get('/admin/accesso')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/login')
                ->where('translations.auth.login_title', 'Accedi all\'area amministrazione'));
    }

    public function test_aucune_donnee_sensible_n_est_partagee(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->where('auth.user', ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]));
    }

    public function test_les_sections_futures_affichent_la_page_provisoire(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/messaggi') // sprint 8 : faq et pagine-legali ont maintenant leurs vraies pages
            ->assertInertia(fn ($page) => $page->component('admin/placeholder')->where('section', 'messages'));
    }

    public function test_les_pages_de_parametres_du_kit_n_existent_plus(): void
    {
        // §5.4 : remplacées par /admin/profilo
        $this->actingAs(User::factory()->create());

        $this->get('/settings/profile')->assertNotFound();
        $this->get('/settings/security')->assertNotFound();
        $this->get('/settings/appearance')->assertNotFound();
    }
}
