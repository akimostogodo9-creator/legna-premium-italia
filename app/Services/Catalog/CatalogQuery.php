<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * F-CAT-02→07 : produits visibles, filtre catégorie, recherche, tri, pagination.
 * Aucune donnée saisie n'est concaténée dans le SQL (étape 7 — injections).
 */
class CatalogQuery
{
    public const SORTS = ['in-evidenza', 'nome', 'prezzo-crescente', 'prezzo-decrescente'];

    /** Longueur minimale d'un mot pour l'index FULLTEXT de MySQL (InnoDB : 3 par défaut). */
    private const FULLTEXT_MIN = 3;

    /** @return LengthAwarePaginator<int, Product> */
    public function paginate(?Category $category, ?string $search, string $sort, int $perPage): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when($category, fn (Builder $q) => $q->where('category_id', $category->id))
            ->tap(fn (Builder $q) => $this->applySearch($q, $search))
            ->tap(fn (Builder $q) => $this->applySort($q, $sort))
            ->paginate($perPage, ['*'], 'pagina')
            ->withQueryString();
    }

    /**
     * F-PROD-09 : jusqu'à 3 produits visibles de la même catégorie.
     *
     * @return Collection<int, Product>
     */
    public function similar(Product $product, int $limit = 3): Collection
    {
        if (! $product->category_id) {
            return new Collection;
        }

        return $this->baseQuery()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * F-HOME-03 / R-ORD-02 : produits en évidence (ou les premiers s'il n'y en a aucun) — utilisé au sprint 7.
     *
     * @return Collection<int, Product>
     */
    public function featured(int $limit): Collection
    {
        $featured = $this->baseQuery()->featured()->ordered()->limit($limit)->get();

        return $featured->isNotEmpty() ? $featured : $this->baseQuery()->ordered()->limit($limit)->get();
    }

    /**
     * Catégories actives qui contiennent au moins un produit visible.
     *
     * @return Collection<int, Category>
     */
    public function categories(): Collection
    {
        return Category::query()
            ->active()
            ->whereHas('products', fn (Builder $q) => $q->where('is_active', true))
            ->ordered()
            ->get();
    }

    public static function normalizeSort(?string $sort): string
    {
        return in_array($sort, self::SORTS, true) ? $sort : 'in-evidenza';
    }

    /** @return Builder<Product> */
    private function baseQuery(): Builder
    {
        return Product::query()
            ->visible()
            ->with(['category:id,name,slug', 'saleUnit:id,name', 'mainImageRelation', 'images']);
    }

    /** @param Builder<Product> $query */
    private function applySearch(Builder $query, ?string $search): void
    {
        // On ne garde que lettres (accents compris), chiffres et espaces
        $words = preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', (string) $search)), -1, PREG_SPLIT_NO_EMPTY);

        if ($words === []) {
            return;
        }

        $allLongEnough = collect($words)->every(fn (string $w) => mb_strlen($w) >= self::FULLTEXT_MIN);

        if ($allLongEnough && DB::getDriverName() === 'mysql') {
            // Mode booléen : chaque mot obligatoire (+) et accepté comme début de mot (*) — « fag » trouve « faggio »
            $expression = collect($words)->map(fn (string $w) => '+'.$w.'*')->implode(' ');
            $query->whereFullText(['name', 'short_description', 'wood_type'], $expression, ['mode' => 'boolean']);

            return;
        }

        // Mots courts (ou autre base de données) : recherche simple, chaque mot doit apparaître
        foreach ($words as $word) {
            $like = '%'.$word.'%';
            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', $like)
                ->orWhere('short_description', 'like', $like)
                ->orWhere('wood_type', 'like', $like));
        }
    }

    /** @param Builder<Product> $query */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'nome' => $query->orderBy('name'),
            // F-CAT-06 : les produits « su richiesta » (sans prix) toujours à la fin
            'prezzo-crescente' => $query->orderByRaw('price IS NULL')->orderBy('price')->orderBy('name'),
            'prezzo-decrescente' => $query->orderByRaw('price IS NULL')->orderByDesc('price')->orderBy('name'),
            default => $query->ordered(),
        };
    }
}
