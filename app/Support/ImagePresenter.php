<?php

namespace App\Support;

use App\Models\ProductImage;
use App\Services\Media\ImageProcessor;
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

    /**
     * Sprint 7 — image de contenu stockée sous « {uuid}-{largeur}.webp » : on reconstruit le srcset
     * à partir des tailles réellement présentes sur le disque (une petite photo n'a pas de 1600).
     *
     * @return array{src: string, srcset: string, width: null, height: null, alt: string}|null
     */
    public static function fromPath(?string $path, string $alt = ''): ?array
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');
        $sources = [];

        if (preg_match('/-\d+\.webp$/', $path)) {
            foreach (ImageProcessor::WIDTHS as $width) {
                $candidate = preg_replace('/-\d+\.webp$/', "-{$width}.webp", $path);

                if ($disk->exists($candidate)) {
                    $sources[$width] = $disk->url($candidate);
                }
            }
        }

        return [
            'src' => $sources[800] ?? $disk->url($path),
            'srcset' => collect($sources)->map(fn (string $url, int $w) => "{$url} {$w}w")->implode(', '),
            'width' => null,
            'height' => null,
            'alt' => $alt,
        ];
    }
}
