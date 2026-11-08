<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Media\ImageProcessor;
use App\Services\Settings\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

/**
 * F-ACONT-05 (sprint 7) : logos, favicon, image de partage, textes généraux.
 * SVG refusé volontairement : un fichier SVG peut contenir du code (étape 7, XSS).
 */
class IdentityController extends Controller
{
    private const IMAGE_KEYS = ['logo_light', 'logo_dark', 'favicon', 'default_share_image'];

    public function __construct(
        private readonly SettingsService $settings,
        private readonly ImageProcessor $images,
    ) {}

    public function edit(): Response
    {
        $disk = Storage::disk('public');

        return Inertia::render('admin/content/identity', [
            'previews' => collect(self::IMAGE_KEYS)->mapWithKeys(fn (string $key) => [
                $key => ($path = $this->settings->get($key)) ? $disk->url($path) : null,
            ]),
            'texts' => [
                'footer_description' => $this->settings->get('footer_description'),
                'delivery_summary' => $this->settings->get('delivery_summary'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $maxKb = $this->settings->get('image_max_mb', 8) * 1024;
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}", 'dimensions:max_width=8000,max_height=8000'];

        $data = $request->validate([
            'logo_light' => $image,
            'logo_dark' => $image,
            'favicon' => [...$image, 'dimensions:min_width=192,min_height=192'],
            'default_share_image' => $image,
            'remove' => ['array'],
            'remove.*' => ['in:'.implode(',', self::IMAGE_KEYS)],
            'footer_description' => ['nullable', 'string', 'max:400'],
            'delivery_summary' => ['nullable', 'string', 'max:400'],
        ]);

        foreach (self::IMAGE_KEYS as $key) {
            $old = $this->settings->get($key);

            if ($request->hasFile($key)) {
                $path = $key === 'favicon'
                    ? $this->storeFavicon($request->file($key))
                    : $this->images->store($request->file($key), 'identity')['path'];
                $this->settings->set($key, $path);
                $this->deleteFile($old);
            } elseif (in_array($key, $data['remove'] ?? [], true)) {
                $this->settings->set($key, null);
                $this->deleteFile($old);
            }
        }

        $this->settings->setMany([
            'footer_description' => $data['footer_description'] ?? null,
            'delivery_summary' => $data['delivery_summary'] ?? null,
        ]);

        return back()->with('success', __('admin.messages.saved'));
    }

    /**
     * Favicon : PNG carré 192 px (compris par tous les navigateurs et par iOS).
     * Adapté à Intervention Image v4 : decodePath() + encode(new PngEncoder).
     */
    private function storeFavicon(UploadedFile $file): string
    {
        $manager = new ImageManager(Driver::class, decodeAnimation: false, strip: true);
        $image = $manager->decodePath($file->getRealPath());
        $image->cover(192, 192);

        $path = 'identity/favicon-'.Str::uuid().'.png';
        Storage::disk('public')->put($path, (string) $image->encode(new PngEncoder));

        return $path;
    }

    private function deleteFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_ends_with($path, '.png')) {
            Storage::disk('public')->delete($path);

            return;
        }

        $this->images->delete($path, array_map(
            fn (int $w) => preg_replace('/-\d+\.webp$/', "-{$w}.webp", $path),
            ImageProcessor::WIDTHS,
        ));
    }
}
