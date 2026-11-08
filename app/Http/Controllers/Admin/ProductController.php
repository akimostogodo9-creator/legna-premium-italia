<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Availability;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SaleUnit;
use App\Services\Catalog\ProductPublisher;
use App\Services\Catalog\ProductService;
use App\Services\Settings\SettingsService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

// F-APROD-01→07
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly ProductPublisher $publisher,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['q', 'category', 'status']);

        $products = Product::query()
            ->with(['category:id,name', 'saleUnit:id,name', 'images'])
            ->when($filters['q'] ?? null, fn ($q, $search) => $q->where('name', 'like', '%'.$search.'%'))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(($filters['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->ordered()
            ->get();

        return Inertia::render('admin/products/index', [
            'filters' => $filters,
            'categories' => Category::ordered()->get(['id', 'name']),
            'products' => $products->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name,
                'price_label' => $p->price ? Money::format($p->price).' / '.$p->saleUnit?->name : null,
                'availability' => $p->availability->label(),
                'is_active' => $p->is_active,
                'is_featured' => $p->is_featured,
                'thumbnail' => $this->thumbnail($p),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/form', [
            'product' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = $this->products->create($request->validated());

        return to_route('admin.products.edit', ['product' => $product->id])
            ->with('success', __('admin.products.photos_after_create'));
    }

    public function edit(Product $product): Response
    {
        $product->load('images');

        return Inertia::render('admin/products/form', [
            'product' => [
                ...$product->only([
                    'id', 'name', 'slug', 'category_id', 'short_description', 'description',
                    'sale_unit_id', 'wood_type', 'dimensions', 'packaging', 'extra_info',
                    'is_featured', 'is_active', 'meta_title', 'meta_description',
                ]),
                // Prix affiché à l'italienne dans le champ (« 129,00 »)
                'price' => $product->price ? str_replace('.', ',', $product->price) : '',
                'availability' => $product->availability->value,
                'can_publish' => $this->publisher->canPublish($product),
                'images' => $product->images->map(fn (ProductImage $image) => [
                    'id' => $image->id,
                    'url' => Storage::disk('public')->url($image->variants['400'] ?? $image->path),
                    'alt_text' => $image->alt_text,
                    'is_main' => $image->is_main,
                ]),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return back()->with('success', __('admin.messages.saved'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product);

        return to_route('admin.products.index')->with('success', __('admin.messages.deleted'));
    }

    /** F-APROD-04 : publier / retirer du site. */
    public function toggleActive(Product $product): RedirectResponse
    {
        $product->is_active
            ? $this->publisher->unpublish($product)
            : $this->publisher->publish($product);

        return back()->with('success', __('admin.messages.saved'));
    }

    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:products,id'],
        ])['ids'];

        $this->publisher->reorder($ids);

        return back()->with('success', __('admin.catalog.order_saved'));
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'categories' => Category::ordered()->get(['id', 'name']),
            'units' => SaleUnit::ordered()->get(['id', 'name']),
            'availabilities' => collect(Availability::cases())
                ->map(fn (Availability $a) => ['value' => $a->value, 'label' => $a->label()]),
            'imageMaxMb' => app(SettingsService::class)->get('image_max_mb', 8),
        ];
    }

    private function thumbnail(Product $product): ?string
    {
        // Les photos sont déjà chargées (with('images')) : pas de requête supplémentaire par produit
        $image = $product->images->firstWhere('is_main', true) ?? $product->images->sortBy('sort_order')->first();

        return $image ? Storage::disk('public')->url($image->variants['400'] ?? $image->path) : null;
    }
}
