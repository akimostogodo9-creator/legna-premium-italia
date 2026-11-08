<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentRepository;
use Inertia\Inertia;
use Inertia\Response;

// F-HOME : première version (Hero, présentation, Perché sceglierci, invito finale) — sprint 3.
// Produits, catégories, livraison et FAQ s'ajoutent au sprint 7.
class HomeController extends Controller
{
    public function __invoke(ContentRepository $content): Response
    {
        $sections = $content->page('home');

        // R-CONTENU-02 : seuls les blocs « Perché sceglierci » actifs
        if (isset($sections['perche_sceglierci']['items'])) {
            $sections['perche_sceglierci']['items'] = array_values(array_filter(
                $sections['perche_sceglierci']['items'],
                fn (array $item) => ($item['visible'] ?? true) && filled($item['title'] ?? null),
            ));
        }

        return Inertia::render('public/home', ['sections' => $sections]);
    }
}
