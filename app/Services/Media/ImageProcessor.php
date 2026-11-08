<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * R-IMG-02 : toute image envoyée est RÉ-ENCODÉE en WebP (supprime tout code caché dans le fichier),
 * en 3 tailles pour les écrans (400, 800, 1600 px de large), sans jamais agrandir une petite image.
 * Noms de fichiers aléatoires (étape 7).
 *
 * Sprint 4 — adapté à Intervention Image v4 (le dossier visait la v3) :
 * decodePath() remplace read(), encode(new WebpEncoder) remplace toWebp().
 * Les métadonnées (EXIF : position GPS, appareil…) sont supprimées et la photo est redressée.
 */
class ImageProcessor
{
    /** Largeurs générées (srcset). La plus grande sert de « path » principal. */
    public const WIDTHS = [400, 800, 1600];

    private const QUALITY = 82;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(Driver::class, autoOrientation: true, decodeAnimation: false, strip: true);
    }

    /**
     * @return array{path: string, variants: array<string, string>, width: int, height: int}
     */
    public function store(UploadedFile $file, string $directory): array
    {
        $disk = Storage::disk('public');
        $source = $file->getRealPath();
        $original = $this->manager->decodePath($source);
        $width = $original->width();
        $height = $original->height();
        $name = (string) Str::uuid();

        $variants = [];

        foreach (self::WIDTHS as $targetWidth) {
            $image = $this->manager->decodePath($source);
            $image->scaleDown(width: $targetWidth);

            $path = "{$directory}/{$name}-{$targetWidth}.webp";
            $disk->put($path, (string) $image->encode(new WebpEncoder(quality: self::QUALITY)));
            $variants[(string) $targetWidth] = $path;

            // Inutile de générer des tailles plus grandes que l'original
            if ($targetWidth >= $width) {
                break;
            }
        }

        return [
            'path' => end($variants),
            'variants' => $variants,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Supprime tous les fichiers d'une image (principal + tailles).
     *
     * @param  array<string, string>|list<string>|null  $variants
     */
    public function delete(?string $path, ?array $variants = null): void
    {
        $files = array_filter(array_unique([$path, ...array_values($variants ?? [])]));

        if ($files !== []) {
            Storage::disk('public')->delete($files);
        }
    }
}
