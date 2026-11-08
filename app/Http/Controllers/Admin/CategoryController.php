<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\Catalog\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

// F-ACAT-01/02
class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(): Response
    {
        return Inertia::render('admin/categories/index', [
            'categories' => Category::query()
                ->ordered()
                ->withCount('products')
                ->get()
                ->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'is_active' => $c->is_active,
                    'products_count' => $c->products_count,
                    'image' => $c->image_path ? Storage::disk('public')->url($c->image_path) : null,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/categories/form', ['category' => null]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->categoryData(), $request->file('image'));

        return to_route('admin.categories.index')->with('success', __('admin.messages.saved'));
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('admin/categories/form', [
            'category' => [
                ...$category->only(['id', 'name', 'slug', 'description', 'meta_title', 'meta_description', 'is_active']),
                'image' => $category->image_path ? Storage::disk('public')->url($category->image_path) : null,
            ],
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->categoryData(), $request->file('image'), $request->boolean('remove_image'));

        return to_route('admin.categories.index')->with('success', __('admin.messages.saved'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->categories->delete($category);

        return back()->with('success', __('admin.messages.deleted'));
    }

    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:categories,id'],
        ])['ids'];

        $this->categories->reorder($ids);

        return back()->with('success', __('admin.catalog.order_saved'));
    }
}
