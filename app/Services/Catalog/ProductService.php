<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Services\Content\HtmlSanitizer;
use App\Services\Media\ImageProcessor;
use App\Support\SlugGenerator;
use Illuminate\Support\Facades\DB;

// Création, modification, suppression d'un produit (étape 3 §13.3)
class ProductService
{
    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
        private readonly ImageProcessor $images,
        private readonly ProductPublisher $publisher,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(array $data): Product
    {
        $data = $this->prepare($data);
        $data['slug'] = SlugGenerator::unique(Product::class, $data['slug'] ?: $data['name']);
        $data['sort_order'] = (int) Product::max('sort_order') + 1;
        $data['is_active'] = false; // R-PROD-02 : publié seulement après ajout des photos

        return Product::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Product $product, array $data): Product
    {
        $data = $this->prepare($data);
        $data['slug'] = SlugGenerator::unique(Product::class, $data['slug'] ?: $data['name'], $product->id);

        $product->update($data);
        $this->publisher->enforce($product); // ex. description courte vidée → retiré du site

        return $product;
    }

    /** Supprime le produit puis ses fichiers (les lignes product_images partent en cascade). */
    public function delete(Product $product): void
    {
        $files = $product->images()->get(['path', 'variants']);

        DB::transaction(fn () => $product->delete());

        foreach ($files as $image) {
            $this->images->delete($image->path, $image->variants);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data): array
    {
        // R-CONTENU-04 : HTML nettoyé à l'enregistrement
        $data['description'] = $this->sanitizer->clean($data['description'] ?? null);

        // R-PRIX-01 : pas de prix → prix NULL (on garde l'unité si l'admin l'a choisie)
        if (($data['price'] ?? null) === null) {
            $data['price'] = null;
        }

        return $data;
    }
}
