<?php

namespace App\Services\Content;

use App\Models\ContentSection;
use App\Services\Media\ImageProcessor;
use Illuminate\Http\UploadedFile;

/**
 * F-ACONT-01→04 (sprint 7) : enregistre une section à partir de son format déclaré dans le registre.
 * - text / textarea : texte brut (React l'échappe à l'affichage)
 * - richtext : HTML nettoyé (R-CONTENU-04)
 * - image : WebP 3 tailles ; l'ancienne image est supprimée
 * - items : blocs « Perché sceglierci » (max 4, R-CONTENU-02)
 */
class ContentSectionService
{
    public const ITEM_ICONS = ['quality', 'trust', 'delivery', 'support', 'nature', 'warmth'];

    public function __construct(
        private readonly ContentSectionRegistry $registry,
        private readonly HtmlSanitizer $sanitizer,
        private readonly ImageProcessor $images,
    ) {}

    /**
     * @param  array<string, mixed>  $input  données validées
     * @param  array<string, UploadedFile|null>  $files  fichiers envoyés, par champ
     * @param  list<string>  $removeImages  champs image à vider
     */
    public function update(ContentSection $section, array $input, array $files, array $removeImages): ContentSection
    {
        $definition = $this->registry->get($section->page, $section->key);
        $content = $section->content ?? [];
        $oldFiles = [];

        foreach ($definition['fields'] as $field => $type) {
            switch ($type) {
                case 'text':
                case 'textarea':
                    $content[$field] = filled($input[$field] ?? null) ? trim((string) $input[$field]) : null;
                    break;

                case 'richtext':
                    $content[$field] = $this->sanitizer->clean($input[$field] ?? null);
                    break;

                case 'image':
                    if (($files[$field] ?? null) instanceof UploadedFile) {
                        $oldFiles[] = $content[$field] ?? null;
                        $content[$field] = $this->images->store($files[$field], 'content')['path'];
                    } elseif (in_array($field, $removeImages, true)) {
                        $oldFiles[] = $content[$field] ?? null;
                        $content[$field] = null;
                    }
                    $content[$field.'_provisional'] = filter_var($input[$field.'_provisional'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    break;

                case 'items':
                    $content[$field] = $this->items($input[$field] ?? []);
                    break;
            }
        }

        $section->update(['content' => $content]);

        foreach (array_filter($oldFiles) as $path) {
            $this->deleteImage($path);
        }

        return $section;
    }

    public function toggleVisibility(ContentSection $section): void
    {
        $section->update(['is_visible' => ! $section->is_visible]);
    }

    /**
     * R-CONTENU-02 : 4 blocs maximum, icône connue, texte brut ; un bloc sans titre est ignoré.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array{icon: string, title: string, text: ?string, visible: bool}>
     */
    private function items(array $items): array
    {
        return collect($items)
            ->take(4)
            ->map(fn (array $item) => [
                'icon' => in_array($item['icon'] ?? null, self::ITEM_ICONS, true) ? $item['icon'] : 'quality',
                'title' => trim((string) ($item['title'] ?? '')),
                'text' => filled($item['text'] ?? null) ? trim((string) $item['text']) : null,
                'visible' => filter_var($item['visible'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ])
            ->filter(fn (array $item) => $item['title'] !== '')
            ->values()
            ->all();
    }

    private function deleteImage(string $path): void
    {
        $variants = array_map(
            fn (int $w) => preg_replace('/-\d+\.webp$/', "-{$w}.webp", $path),
            ImageProcessor::WIDTHS,
        );

        $this->images->delete($path, $variants);
    }
}
