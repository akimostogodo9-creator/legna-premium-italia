<?php

namespace App\Services\Content;

use App\Models\ContentSection;
use Illuminate\Support\Facades\Storage;

/**
 * Lecture des sections de contenu visibles d'une page (R-CONTENU-01) — sprint 3.
 * Les champs « image » sont convertis en URL publique.
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
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function withImageUrls(string $page, string $key, array $content): array
    {
        $fields = $this->registry->get($page, $key)['fields'];

        foreach ($fields as $name => $type) {
            if ($type === 'image') {
                $content[$name] = ! empty($content[$name]) ? Storage::disk('public')->url($content[$name]) : null;
            }
        }

        return $content;
    }
}
