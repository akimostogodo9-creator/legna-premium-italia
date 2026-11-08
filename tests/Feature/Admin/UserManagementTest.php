<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

// F-AUSR-01/02, R-ADM-02 : comptes administrateurs (sprint 2)
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_liste_des_administrateurs_s_affiche(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/utenti')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/users/index')
                ->has('users', 1)
                ->where('users.0.is_current', true));
    }

    public function test_creer_un_admin_envoie_un_lien_pour_choisir_le_mot_de_passe(): void
    {
        Notification::fake();
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/utenti', ['name' => 'Marco Rossi', 'email' => 'marco@example.test'])
            ->assertRedirect('/admin/utenti');

        $created = User::where('email', 'marco@example.test')->firstOrFail();
        $this->assertTrue($created->is_active);
        $this->assertNotNull($created->email_verified_at);

        Notification::assertSentTo($created, ResetPassword::class, function (ResetPassword $notification) use ($created) {
            // Le lien pointe vers la page italienne de l'admin (sprint 0)
            $url = $notification->toMail($created)->actionUrl;

            return str_contains($url, '/admin/reimposta-password/');
        });
    }

    public function test_e_mail_deja_utilisee_refusee(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/utenti', ['name' => 'Doppio', 'email' => $admin->email])
            ->assertSessionHasErrors('email');
    }

    public function test_impossible_de_se_desactiver_ou_se_supprimer_soi_meme(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(); // un autre admin actif existe

        $this->actingAs($admin)->patch("/admin/utenti/{$admin->id}/stato")->assertSessionHasErrors('user');
        $this->actingAs($admin)->delete("/admin/utenti/{$admin->id}")->assertSessionHasErrors('user');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_desactiver_puis_reactiver_un_autre_admin(): void
    {
        $admin = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)->patch("/admin/utenti/{$other->id}/stato")->assertSessionHasNoErrors();
        $this->assertFalse($other->fresh()->is_active);

        $this->actingAs($admin)->patch("/admin/utenti/{$other->id}/stato")->assertSessionHasNoErrors();
        $this->assertTrue($other->fresh()->is_active);
    }

    public function test_le_dernier_admin_actif_ne_peut_jamais_etre_desactive(): void
    {
        // Cas limite testé directement sur le service (impossible à provoquer depuis l'interface,
        // car un admin désactivé ne peut plus agir) — R-ADM-02
        $actor = User::factory()->inactive()->create();
        $lastActive = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(AdminUserService::class)->toggleActive($lastActive, $actor);
    }

    public function test_supprimer_un_autre_admin(): void
    {
        $admin = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)->delete("/admin/utenti/{$other->id}")->assertSessionHasNoErrors();

        $this->assertModelMissing($other);
    }
}
