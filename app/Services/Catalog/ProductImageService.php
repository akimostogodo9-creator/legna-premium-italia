<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Media\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

// R-IMG-01→03
class ProductImageService
{
    public function __construct(
        private readonly ImageProcessor $processor,
        private readonly ProductPublisher $publisher,
    ) {}

    /** @param UploadedFile[] $files */
    public function upload(Product $product, array $files): void
    {
        $nextOrder = (int) $product->images()->max('sort_order') + 1;
        $hasMain = $product->images()->where('is_main', true)->exists();

        foreach ($files as $file) {
            $stored = $this->processor->store($file, "products/{$product->id}");

            $product->images()->create([
                ...$stored,
                'is_main' => ! $hasMain, // la première photo d'un produit devient la principale
                'sort_order' => $nextOrder++,
            ]);

            $hasMain = true;
        }
    }

    /** R-IMG-01 : une seule photo principale par produit. */
    public function setMain(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            ProductImage::where('product_id', $image->product_id)->update(['is_main' => false]);
            $image->update(['is_main' => true]);
        });
    }

    public function updateAlt(ProductImage $image, ?string $alt): void
    {
        $image->update(['alt_text' => $alt]);
    }

    /** @param list<int> $ids */
    public function reorder(Product $product, array $ids): void
    {
        DB::transaction(function () use ($product, $ids) {
            foreach (array_values($ids) as $position => $id) {
                // whereKey limité aux photos DE CE produit : impossible de toucher celles d'un autre
                $product->images()->whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }

    /**
     * Supprime une photo. Si c'était la principale, la suivante le devient.
     * Renvoie vrai si le produit a été retiré du site faute de photo (R-PROD-02).
     */
    public function delete(ProductImage $image): bool
    {
        $product = $image->product;
        $wasMain = $image->is_main;
        $path = $image->path;
        $variants = $image->variants;

        DB::transaction(function () use ($image, $product, $wasMain) {
            $image->delete();

            if ($wasMain) {
                $product->images()->orderBy('sort_order')->first()?->update(['is_main' => true]);
            }
        });

        $this->processor->delete($path, $variants);

        return $this->publisher->enforce($product->fresh());
    }
}
