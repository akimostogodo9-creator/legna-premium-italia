<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// R-PROD-02 (conditions de publication) et R-ORD-01 (ordre)
class ProductPublisher
{
    /** Vrai si le produit remplit les conditions pour être publié. */
    public function canPublish(Product $product): bool
    {
        return filled($product->name)
            && filled($product->short_description)
            && $product->images()->exists();
    }

    public function publish(Product $product): void
    {
        if (! $this->canPublish($product)) {
            throw ValidationException::withMessages(['product' => __('admin.products.cannot_publish')]);
        }

        $product->update(['is_active' => true]);
    }

    public function unpublish(Product $product): void
    {
        $product->update(['is_active' => false]);
    }

    /** Après suppression de photos : un produit publié sans photo est retiré du site. Renvoie vrai si retiré. */
    public function enforce(Product $product): bool
    {
        if ($product->is_active && ! $this->canPublish($product)) {
            $product->update(['is_active' => false]);

            return true;
        }

        return false;
    }

    /** @param list<int> $ids */
    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $position => $id) {
                Product::whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }
}
