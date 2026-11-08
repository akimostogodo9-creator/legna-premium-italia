<?php

namespace App\Support;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

/**
 * F-SEO-07 / R-IMG : données d'image pour React — src, srcset (400/800/1600), dimensions (évite
 * les sauts de mise en page) et texte alternatif (R-IMG-03 : nom du produit si vide).
 */
final class ImagePresenter
{
    /** @return array{src: string, srcset: string, width: ?int, height: ?int, alt: string}|null */
    public static function present(?ProductImage $image, string $fallbackAlt): ?array
    {
        if ($image === null) {
            return null;
        }

        $disk = Storage::disk('public');
        $variants = $image->variants ?: [];

        $srcset = collect($variants)
            ->map(fn (string $path, string|int $width) => $disk->url($path).' '.$width.'w')
            ->implode(', ');

        return [
            // Taille moyenne par défaut : le navigateur choisit ensuite grâce au srcset
            'src' => $disk->url($variants['800'] ?? $image->path),
            'srcset' => $srcset,
            'width' => $image->width,
            'height' => $image->height,
            'alt' => filled($image->alt_text) ? $image->alt_text : $fallbackAlt,
        ];
    }
}
