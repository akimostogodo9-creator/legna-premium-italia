<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettingsService $settings;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create(['key' => 'products_per_page', 'value' => '12', 'type' => 'int', 'group' => 'catalogo']);
        Setting::create(['key' => 'show_notice', 'value' => '1', 'type' => 'bool', 'group' => 'catalogo']);
        Setting::create(['key' => 'order_email', 'value' => null, 'type' => 'email', 'group' => 'contatti']);
        Setting::create(['key' => 'items', 'value' => '["a","b"]', 'type' => 'json', 'group' => 'test']);

        $this->settings = app(SettingsService::class);
        $this->settings->flush();
    }

    public function test_les_types_sont_convertis(): void
    {
        $this->assertSame(12, $this->settings->get('products_per_page'));
        $this->assertTrue($this->settings->get('show_notice'));
        $this->assertSame(['a', 'b'], $this->settings->get('items'));
    }

    public function test_valeur_par_defaut_si_absent_ou_vide(): void
    {
        $this->assertSame('x', $this->settings->get('cle_inconnue', 'x'));
        $this->assertSame('defaut', $this->settings->get('order_email', 'defaut'));
        $this->assertFalse($this->settings->filled('order_email'));
    }

    public function test_set_met_a_jour_et_vide_le_cache(): void
    {
        $this->assertSame(12, $this->settings->get('products_per_page')); // met en cache

        $this->settings->set('products_per_page', 24);
        $this->settings->set('order_email', '  ordini@example.test  ');

        $this->assertSame(24, $this->settings->get('products_per_page'));
        $this->assertSame('ordini@example.test', $this->settings->get('order_email'));
        $this->assertTrue($this->settings->filled('order_email'));
    }

    public function test_set_refuse_une_cle_inconnue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->settings->set('faute_de_frappe', 'x');
    }

    public function test_group_renvoie_les_valeurs_d_un_groupe(): void
    {
        $this->assertSame(['products_per_page' => 12, 'show_notice' => true], $this->settings->group('catalogo'));
    }
}
