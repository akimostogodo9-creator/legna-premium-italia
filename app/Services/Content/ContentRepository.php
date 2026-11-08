<?php

namespace App\Services\Content;

use App\Models\ContentSection;
use App\Support\ImagePresenter;

/**
 * Lecture des sections de contenu visibles d'une page (R-CONTENU-01) — sprint 3.
 * Les champs « image » deviennent un objet image (src, srcset, alt) — sprint 7.
 */
class ContentRepository
{
    public function __construct(private readonly ContentSectionRegistry $registry) {}

    /** @return array<string, array<string, mixed>> ['hero' => [...], 'presentazione' => [...]] — sections masquées exclues */
    public function page(string $page): array
    {
        return ContentSection::query()
            ->forPage($page)
            ->visible()
            ->get()
            ->mapWithKeys(fn (ContentSection $section) => [
                $section->key => $this->withImageUrls($page, $section->key, $section->content ?? []),
            ])
            ->all();
    }

    /**
     * Sprint 7 : champs « image » → objet image (src, srcset, alt) prêt pour <ResponsiveImage>.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function withImageUrls(string $page, string $key, array $content): array
    {
        $fields = $this->registry->get($page, $key)['fields'];

        foreach ($fields as $name => $type) {
            if ($type === 'image') {
                $alt = (string) ($content[$name.'_alt'] ?? $content['image_alt'] ?? '');
                $content[$name] = ImagePresenter::fromPath($content[$name] ?? null, $alt);
            }
        }

        return $content;
    }
}
