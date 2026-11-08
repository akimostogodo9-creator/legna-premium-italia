import type { ProductCard as ProductCardData } from '@/types/catalog';
import { ProductCard } from './product-card';

// F-CAT-02 : 1 colonne (téléphone), 2 (tablette), 3 (ordinateur)
export function ProductGrid({ products }: { products: ProductCardData[] }) {
    return (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
            {products.map((product) => (
                <ProductCard key={product.id} product={product} />
            ))}
        </div>
    );
}
