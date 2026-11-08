<?php

namespace Tests\Feature\Public;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Services\Settings\SettingsService;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// F-CONT-01→07, R-CONT-01→07 — sprint 9
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([SettingSeeder::class, ContentSectionSeeder::class, LegalPageSeeder::class]);
        app(SettingsService::class)->set('contact_email', 'info@example.test');
        RateLimiter::clear('contact:'.sha1('127.0.0.1'));
        Mail::fake();
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function payload(array $override = []): array
    {
        return [
            'name' => 'Mario Rossi',
            'email' => 'Mario@Example.test',
            'phone' => '+39 340 123 4567',
            'subject' => 'delivery',
            'message' => 'Consegnate anche a Bergamo? Mi servirebbero due bancali.',
            'privacy' => true,
            'website' => '',
            ...$override,
        ];
    }

    public function test_la_page_contatti_s_affiche(): void
    {
        $this->get('/contatti')->assertOk()->assertInertia(fn ($page) => $page
            ->component('public/contact')
            ->where('formEnabled', true)
            ->has('subjects', 4));
    }

    public function test_message_enregistre_et_envoye_au_vendeur(): void
    {
        $this->post('/contatti', $this->payload())->assertSessionHas('success');

        $message = ContactMessage::firstOrFail();
        $this->assertSame('mario@example.test', $message->email);
        $this->assertSame('new', $message->status->value);
        $this->assertNotNull($message->privacy_accepted_at);

        Mail::assertQueued(ContactMessageReceived::class, fn ($mail) => $mail->hasTo('info@example.test')
            && $mail->hasReplyTo('mario@example.test'));
    }

    public function test_consentement_obligatoire_et_message_trop_court(): void
    {
        $this->post('/contatti', $this->payload(['privacy' => false, 'message' => 'Ciao']))
            ->assertSessionHasErrors(['privacy', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_robot_ignore_en_silence(): void
    {
        $this->post('/contatti', $this->payload(['website' => 'http://spam.test']))->assertSessionHas('success');

        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingQueued();
    }

    public function test_limite_d_envois_par_visiteur(): void
    {
        app(SettingsService::class)->set('contact_rate_limit_per_hour', 2);

        $this->post('/contatti', $this->payload())->assertSessionHasNoErrors();
        $this->post('/contatti', $this->payload())->assertSessionHasNoErrors();
        $this->post('/contatti', $this->payload())->assertSessionHasErrors('message');

        $this->assertSame(2, ContactMessage::count());
    }

    public function test_formulaire_indisponible_sans_adresse_de_reception(): void
    {
        app(SettingsService::class)->set('contact_email', null);

        $this->get('/contatti')->assertInertia(fn ($page) => $page->where('formEnabled', false));
        $this->post('/contatti', $this->payload())->assertNotFound();
    }

    public function test_formulaire_desactive_dans_l_admin(): void
    {
        app(SettingsService::class)->set('contact_form_enabled', false);

        $this->get('/contatti')->assertInertia(fn ($page) => $page->where('formEnabled', false));
    }

    public function test_le_html_du_visiteur_est_stocke_comme_texte_et_echappe_dans_l_email(): void
    {
        $this->post('/contatti', $this->payload(['message' => '<script>alert(1)</script> vorrei informazioni sui prezzi']));

        $message = ContactMessage::firstOrFail();
        $html = (new ContactMessageReceived($message))->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_aucune_adresse_ip_stockee(): void
    {
        $this->assertNotContains('ip', Schema::getColumnListing('contact_messages'));
        $this->assertNotContains('ip_address', Schema::getColumnListing('contact_messages'));
    }

    public function test_suppression_automatique_apres_24_mois(): void
    {
        $old = ContactMessage::factory()->create(['created_at' => now()->subMonths(25)]);
        $recent = ContactMessage::factory()->create(['created_at' => now()->subMonths(23)]);

        $this->artisan('legna:purge-contact-messages')->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }
}
