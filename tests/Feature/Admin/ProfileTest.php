<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// F-AUTH-04 : mon profil (sprint 2)
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_profil_s_affiche(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/profilo')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/profile/edit')
                ->where('profile', ['name' => $admin->name, 'email' => $admin->email]));
    }

    public function test_modifier_nom_et_email(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->patch('/admin/profilo', ['name' => 'Nuovo Nome', 'email' => 'nuovo@example.test'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Nuovo Nome', $admin->fresh()->name);
        $this->assertSame('nuovo@example.test', $admin->fresh()->email);
    }

    public function test_changer_le_mot_de_passe_exige_l_ancien(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('vecchia-password-123')]);

        $this->actingAs($admin)
            ->put('/admin/profilo/password', [
                'current_password' => 'sbagliata',
                'password' => 'nuova-password-456',
                'password_confirmation' => 'nuova-password-456',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($admin)
            ->put('/admin/profilo/password', [
                'current_password' => 'vecchia-password-123',
                'password' => 'nuova-password-456',
                'password_confirmation' => 'nuova-password-456',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nuova-password-456', $admin->fresh()->password));
    }

    public function test_mot_de_passe_trop_faible_refuse(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('vecchia-password-123')]);

        foreach (['corta', 'solo-lettere-senza-numeri', '123456789012'] as $weak) {
            $this->actingAs($admin)
                ->put('/admin/profilo/password', [
                    'current_password' => 'vecchia-password-123',
                    'password' => $weak,
                    'password_confirmation' => $weak,
                ])
                ->assertSessionHasErrors('password');
        }
    }
}
