<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

// R-LEG-01/02 : 5 pages légales créées VIDES et NON publiées (contenu à fournir, relu par un professionnel)
class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'termini-e-condizioni' => 'Termini e condizioni',
            'privacy-policy' => 'Privacy Policy',
            'cookie-policy' => 'Cookie Policy',
            'informazioni-azienda' => "Informazioni sull'azienda",
            'reclami' => 'Reclami',
        ];

        foreach ($pages as $slug => $title) {
            Page::firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => null, 'is_published' => false],
            );
        }
    }
}
