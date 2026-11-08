<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCardResource;
use App\Models\Faq;
use App\Services\Catalog\CatalogQuery;
use App\Services\Content\ContentRepository;
use App\Services\Settings\SettingsService;
use Inertia\Inertia;
use Inertia\Response;

// F-HOME-01→08 (sprint 7) : chaque section est absente des props si l'admin l'a masquée
class HomeController extends Controller
{
    public function __invoke(ContentRepository $content, CatalogQuery $catalog, SettingsService $settings): Response
    {
        $sections = $content->page('home');

        // R-CONTENU-02 : seuls les blocs actifs et titrés
        if (isset($sections['perche_sceglierci']['items'])) {
            $sections['perche_sceglierci']['items'] = array_values(array_filter(
                $sections['perche_sceglierci']['items'],
                fn (array $item) => ($item['visible'] ?? true) && filled($item['title'] ?? null),
            ));
        }

        return Inertia::render('public/home', [
            'sections' => $sections,
            // R-ORD-02 : produits « in evidenza » (sinon les premiers)
            'featured' => isset($sections['prodotti'])
                ? ProductCardResource::collection($catalog->featured((int) $settings->get('featured_count', 4)))->resolve()
                : [],
            'categories' => isset($sections['categorie'])
                ? CategoryResource::collection($catalog->categories())->resolve()
                : [],
            // R-LIV-04 : résumé de livraison (section masquée si vide)
            'deliverySummary' => $settings->get('delivery_summary'),
            // F-HOME-07 : FAQ actives marquées « mostra in Home »
            'faqs' => isset($sections['faq'])
                ? Faq::active()->where('show_on_home', true)->ordered()->limit(4)->get(['id', 'question', 'answer'])
                : [],
        ]);
    }
}
