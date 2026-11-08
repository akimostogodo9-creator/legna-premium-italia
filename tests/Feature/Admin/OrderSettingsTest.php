<?php

namespace Tests\Feature\Admin;

use App\Http\Requests\Admin\OrderSettingsRequest;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// F-ASET-02/03/04 — sprint 6
class OrderSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
        $this->actingAs(User::factory()->create());
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function payload(array $override = []): array
    {
        $settings = app(SettingsService::class);

        return [
            ...collect(OrderSettingsRequest::TEMPLATE_FIELDS)
                ->mapWithKeys(fn ($key) => [$key => $settings->get($key)])->all(),
            'order_email' => 'ordini@example.test',
            'contact_email' => null,
            'whatsapp_number' => '+39 340 123 4567',
            ...$override,
        ];
    }

    public function test_la_page_s_affiche_avec_les_variables(): void
    {
        $this->withoutVite();

        $this->get('/admin/impostazioni')->assertOk()->assertInertia(fn ($page) => $page
            ->component('admin/settings/index')
            ->where('variables', ['nome_prodotto', 'categoria', 'prezzo', 'unita', 'url_prodotto'])
            ->has('values.order_body_template'));
    }

    public function test_enregistrer_les_contacts(): void
    {
        $this->put('/admin/impostazioni/contatti-e-ordini', $this->payload())->assertSessionHasNoErrors();

        $this->assertSame('ordini@example.test', app(SettingsService::class)->get('order_email'));
        $this->assertSame('+39 340 123 4567', app(SettingsService::class)->get('whatsapp_number'));
    }

    public function test_vider_l_email_masque_le_bouton(): void
    {
        $this->put('/admin/impostazioni/contatti-e-ordini', $this->payload());
        $this->put('/admin/impostazioni/contatti-e-ordini', $this->payload(['order_email' => '']))->assertSessionHasNoErrors();

        $this->assertFalse(app(SettingsService::class)->filled('order_email'));
    }

    public function test_numero_whatsapp_invalide_refuse(): void
    {
        $this->put('/admin/impostazioni/contatti-e-ordini', $this->payload(['whatsapp_number' => '340 123']))
            ->assertSessionHasErrors('whatsapp_number');
    }

    public function test_variable_inconnue_refusee(): void
    {
        $this->put('/admin/impostazioni/contatti-e-ordini', $this->payload(['order_body_template' => 'Ciao {nome}']))
            ->assertSessionHasErrors('order_body_template');
    }

    public function test_email_invalide_refuse(): void
    {
        $this->put('/admin/impostazioni/contatti-e-ordini', $this->payload(['order_email' => 'non-una-email']))
            ->assertSessionHasErrors('order_email');
    }
}
