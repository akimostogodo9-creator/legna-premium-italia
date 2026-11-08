<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\CatalogQuery;
use App\Services\Content\ContentRepository;
use App\Services\Settings\SettingsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// F-CAT et F-PROD — sprint 5
class ProductController extends Controller
{
    public function __construct(
        private readonly CatalogQuery $catalog,
        private readonly SettingsService $settings,
    ) {}

    /** /prodotti */
    public function index(Request $request, ContentRepository $content): Response
    {
        return $this->renderCatalog($request, $content, null);
    }

    /** /prodotti/categoria/{slug} — URL indexable par Google (F-CAT-03) */
    public function category(Request $request, ContentRepository $content, Category $category): Response
    {
        abort_unless($category->is_active, 404); // R-CAT-03

        return $this->renderCatalog($request, $content, $category);
    }

    /** /prodotti/{slug} */
    public function show(Request $request, Product $product): Response
    {
        // R-PROD-03 / R-CAT-03 : invisible pour le public ; aperçu réservé aux admins connectés et actifs (F-APROD-08)
        $visible = Product::visible()->whereKey($product->id)->exists();
        $isAdmin = (bool) $request->user()?->is_active;
        abort_unless($visible || $isAdmin, 404);

        $product->load(['category', 'saleUnit', 'images', 'mainImageRelation']);

        return Inertia::render('public/product-show', [
            'product' => ProductDetailResource::make($product)->resolve(),
            'preview' => ! $visible,
            'similar' => ProductCardResource::collection($this->catalog->similar($product))->resolve(),
            'showIndicativeNotice' => (bool) $this->settings->get('show_indicative_price_notice', true),
            'deliverySummary' => $this->settings->get('delivery_summary'),
        ]);
    }

    private function renderCatalog(Request $request, ContentRepository $content, ?Category $category): Response
    {
        $search = mb_substr((string) $request->query('q', ''), 0, 100);
        $sort = CatalogQuery::normalizeSort($request->query('ordina'));
        $perPage = max(1, (int) $this->settings->get('products_per_page', 12));

        $products = $this->catalog->paginate($category, $search, $sort, $perPage);

        return Inertia::render('public/catalog', [
            'intro' => $content->page('prodotti')['intro'] ?? null,
            'categories' => CategoryResource::collection($this->catalog->categories())->resolve(),
            'currentCategory' => $category ? CategoryResource::make($category)->resolve() : null,
            'filters' => ['q' => $search, 'ordina' => $sort],
            'products' => $this->paginated($products),
            'resultsLabel' => trans_choice('public.catalog.results', $products->total(), ['count' => $products->total()]),
        ]);
    }

    /**
     * Pagination simple et indexable (F-CAT-07) : liens réels « ?pagina=2 ».
     *
     * @param  LengthAwarePaginator<int, Product>  $paginator
     * @return array<string, mixed>
     */
    private function paginated(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => ProductCardResource::collection($paginator->items())->resolve(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'prev_url' => $paginator->previousPageUrl(),
            'next_url' => $paginator->nextPageUrl(),
            'pages' => collect(range(1, $paginator->lastPage()))
                ->map(fn (int $page) => ['page' => $page, 'url' => $paginator->url($page)])
                ->all(),
        ];
    }
}
