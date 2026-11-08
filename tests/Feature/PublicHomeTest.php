<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_d_accueil_s_affiche_en_italien(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="it"', false)
            ->assertInertia(fn ($page) => $page
                ->component('public/home')
                ->where('title', 'Sito in costruzione'));
    }

    // R-ADM-01 : aucune inscription publique
    public function test_l_inscription_publique_n_existe_pas(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/admin/register')->assertNotFound();
    }

    public function test_l_admin_redirige_vers_la_connexion(): void
    {
        $this->get('/admin')->assertRedirect('/admin/accesso');
    }

    public function test_la_page_de_connexion_admin_s_affiche(): void
    {
        $this->withoutVite();

        $this->get('/admin/accesso')->assertOk();
    }
}
