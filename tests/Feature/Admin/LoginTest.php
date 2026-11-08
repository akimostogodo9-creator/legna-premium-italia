<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// §4 : sécurité de la connexion (sprint 2)
class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_connexion_reussie_et_derniere_connexion_enregistree(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password-di-test-123')]);

        $this->post('/admin/accesso', ['email' => $user->email, 'password' => 'password-di-test-123'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $user = User::factory()->inactive()->create(['password' => bcrypt('password-di-test-123')]);

        $this->post('/admin/accesso', ['email' => $user->email, 'password' => 'password-di-test-123'])
            ->assertSessionHasErrors(['email' => __('admin.auth.inactive')]);

        $this->assertGuest();
    }

    public function test_un_mauvais_mot_de_passe_donne_un_message_generique(): void
    {
        // Même message qu'un compte inexistant : on ne révèle pas si l'e-mail existe
        $user = User::factory()->inactive()->create();

        $this->post('/admin/accesso', ['email' => $user->email, 'password' => 'sbagliata'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_la_sixieme_tentative_est_bloquee(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/accesso', ['email' => $user->email, 'password' => 'sbagliata']);
        }

        $response = $this->post('/admin/accesso', ['email' => $user->email, 'password' => 'sbagliata']);

        // Fortify répond 429 « Too Many Requests »
        $response->assertTooManyRequests();
        $this->assertGuest();
    }

    public function test_l_inscription_publique_n_existe_pas(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
        $this->get('/admin/register')->assertNotFound();
    }
}
