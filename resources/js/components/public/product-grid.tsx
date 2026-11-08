import type { ProductCard as ProductCardData } from '@/types/catalog';
import { OrderButton } from './order-button';
import { ProductCard } from './product-card';

// F-CAT-02 : 1 colonne (téléphone), 2 (tablette), 3 (ordinateur) — sprint 6 : bouton « Ordina » sur chaque carte
export function ProductGrid({ products }: { products: ProductCardData[] }) {
    return (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
            {products.map((product) => (
                <ProductCard
                    key={product.id}
                    product={product}
                    actions={
                        <OrderButton
                            order={product.order}
                            productId={product.id}
                        />
                    }
                />
            ))}
        </div>
    );
}
