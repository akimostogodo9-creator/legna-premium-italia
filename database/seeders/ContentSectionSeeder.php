<?php

namespace Database\Seeders;

use App\Models\ContentSection;
use App\Services\Content\ContentSectionRegistry;
use Illuminate\Database\Seeder;

// Crée toutes les sections avec les textes du CDC ; ne remplace JAMAIS une section déjà modifiée
class ContentSectionSeeder extends Seeder
{
    public function run(ContentSectionRegistry $registry): void
    {
        foreach ($registry->all() as $page => $sections) {
            $order = 0;

            foreach ($sections as $key => $definition) {
                ContentSection::firstOrCreate(
                    ['page' => $page, 'key' => $key],
                    [
                        'content' => $definition['defaults'],
                        'is_visible' => true,
                        'sort_order' => $order++,
                    ],
                );
            }
        }
    }
}
