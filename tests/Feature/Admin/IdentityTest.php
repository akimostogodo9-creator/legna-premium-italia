<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Settings\SettingsService;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// F-ACONT-05 — sprint 7
class IdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_logo_favicon_et_textes(): void
    {
        $this->post('/admin/contenuti/identita', [
            'logo_light' => UploadedFile::fake()->image('logo.png', 600, 200),
            'favicon' => UploadedFile::fake()->image('icon.png', 512, 512),
            'delivery_summary' => 'Consegna in tutta la provincia.',
        ])->assertSessionHasNoErrors();

        $settings = app(SettingsService::class);
        $this->assertStringEndsWith('.webp', $settings->get('logo_light'));
        $this->assertStringEndsWith('.png', $settings->get('favicon'));
        Storage::disk('public')->assertExists($settings->get('favicon'));
        $this->assertSame('Consegna in tutta la provincia.', $settings->get('delivery_summary'));
    }

    public function test_le_logo_arrive_sur_le_site(): void
    {
        $this->withoutVite();
        $this->post('/admin/contenuti/identita', [
            'logo_light' => UploadedFile::fake()->image('logo.png', 600, 200),
        ]);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('site.logo.light', fn ($url) => str_ends_with($url, '.webp')));
    }

    public function test_svg_et_favicon_trop_petit_refuses(): void
    {
        $this->post('/admin/contenuti/identita', [
            'logo_light' => UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'),
            'favicon' => UploadedFile::fake()->image('icon.png', 64, 64),
        ])->assertSessionHasErrors(['logo_light', 'favicon']);
    }

    public function test_retirer_le_logo(): void
    {
        $this->post('/admin/contenuti/identita', ['logo_dark' => UploadedFile::fake()->image('logo.png', 400, 200)]);
        $path = app(SettingsService::class)->get('logo_dark');

        $this->post('/admin/contenuti/identita', ['remove' => ['logo_dark']])->assertSessionHasNoErrors();

        $this->assertNull(app(SettingsService::class)->get('logo_dark'));
        Storage::disk('public')->assertMissing($path);
    }
}
