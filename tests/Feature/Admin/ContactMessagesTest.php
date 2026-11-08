<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// F-AMSG-01→03, F-ASET-06 — sprint 9
class ContactMessagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(SettingSeeder::class);
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_la_liste_s_affiche(): void
    {
        ContactMessage::factory()->count(3)->create();

        $this->get('/admin/messaggi')->assertOk()->assertInertia(fn ($p) => $p
            ->component('admin/messages/index')
            ->has('messages.data', 3)
            ->where('retentionMonths', 24));
    }

    public function test_ouvrir_un_message_le_marque_comme_lu(): void
    {
        $message = ContactMessage::factory()->create();

        $this->get("/admin/messaggi/{$message->id}")->assertOk()
            ->assertInertia(fn ($p) => $p->component('admin/messages/show')->where('message.id', $message->id));

        $this->assertSame('read', $message->fresh()->status->value);
    }

    public function test_marquer_comme_gere_puis_rouvrir(): void
    {
        $message = ContactMessage::factory()->create();

        $this->patch("/admin/messaggi/{$message->id}/gestito");
        $this->assertSame('handled', $message->fresh()->status->value);
        $this->assertSame($this->admin->id, $message->fresh()->handled_by);

        $this->patch("/admin/messaggi/{$message->id}/gestito");
        $this->assertSame('read', $message->fresh()->status->value);
        $this->assertNull($message->fresh()->handled_by);
    }

    public function test_recherche_et_filtre(): void
    {
        ContactMessage::factory()->create(['name' => 'Giulia Bianchi', 'subject' => 'prices']);
        ContactMessage::factory()->create(['name' => 'Luca Verdi', 'subject' => 'delivery']);

        $this->get('/admin/messaggi?q=Giulia')->assertInertia(fn ($p) => $p->has('messages.data', 1));
        $this->get('/admin/messaggi?subject=delivery')->assertInertia(fn ($p) => $p->has('messages.data', 1)->where('messages.data.0.name', 'Luca Verdi'));
    }

    public function test_suppression_unitaire_et_multiple(): void
    {
        $messages = ContactMessage::factory()->count(4)->create();

        $this->delete("/admin/messaggi/{$messages[0]->id}")->assertRedirect('/admin/messaggi');
        $this->post('/admin/messaggi/elimina', ['ids' => [$messages[1]->id, $messages[2]->id]])->assertSessionHasNoErrors();

        $this->assertSame(1, ContactMessage::count());
    }

    public function test_badge_non_lus(): void
    {
        ContactMessage::factory()->count(2)->create();

        $this->get('/admin')->assertInertia(fn ($p) => $p->where('admin.unread_messages', 2));
        $this->get('/')->assertInertia(fn ($p) => $p->where('admin', null)); // rien côté site public
    }

    public function test_parametres_du_formulaire(): void
    {
        $this->put('/admin/impostazioni/modulo', [
            'contact_form_enabled' => false,
            'contact_rate_limit_per_hour' => 3,
            'contact_retention_months' => 12,
        ])->assertSessionHasNoErrors();

        $settings = app(SettingsService::class);
        $this->assertFalse($settings->get('contact_form_enabled'));
        $this->assertSame(3, $settings->get('contact_rate_limit_per_hour'));
        $this->assertSame(12, $settings->get('contact_retention_months'));

        $this->put('/admin/impostazioni/modulo', ['contact_rate_limit_per_hour' => 0, 'contact_retention_months' => 500])
            ->assertSessionHasErrors(['contact_rate_limit_per_hour', 'contact_retention_months']);
    }
}
