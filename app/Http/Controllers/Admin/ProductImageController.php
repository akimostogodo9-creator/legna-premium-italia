<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductImageUploadRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Catalog\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// F-APROD-03
class ProductImageController extends Controller
{
    public function __construct(private readonly ProductImageService $images) {}

    public function store(ProductImageUploadRequest $request, Product $product): RedirectResponse
    {
        $this->images->upload($product, $request->file('photos'));

        return back()->with('success', __('admin.messages.saved'));
    }

    public function reorder(Request $request, Product $product): RedirectResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $this->images->reorder($product, $ids);

        return back()->with('success', __('admin.catalog.order_saved'));
    }

    public function setMain(ProductImage $image): RedirectResponse
    {
        $this->images->setMain($image);

        return back()->with('success', __('admin.messages.saved'));
    }

    public function updateAlt(Request $request, ProductImage $image): RedirectResponse
    {
        $data = $request->validate(['alt_text' => ['nullable', 'string', 'max:191']]);

        $this->images->updateAlt($image, $data['alt_text'] ?? null);

        return back()->with('success', __('admin.messages.saved'));
    }

    public function destroy(ProductImage $image): RedirectResponse
    {
        $unpublished = $this->images->delete($image);

        return back()->with(
            $unpublished ? 'error' : 'success',
            $unpublished ? __('admin.products.unpublished_no_photo') : __('admin.messages.deleted'),
        );
    }
}
