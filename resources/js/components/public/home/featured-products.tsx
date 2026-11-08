import { ButtonLink } from '@/components/public/button-link';
import { ProductGrid } from '@/components/public/product-grid';
import products from '@/routes/products';
import type { ProductCard } from '@/types/catalog';

type Content = {
    title: string;
    subtitle?: string | null;
    button_label?: string | null;
};

// F-HOME-03 (sprint 7) : produits en évidence (avec « Ordina ») + lien vers le catalogue
export function FeaturedProducts({
    content,
    products: list,
}: {
    content: Content;
    products: ProductCard[];
}) {
    if (list.length === 0) {
        return null;
    }

    return (
        <section id="prodotti" className="scroll-mt-24 bg-crema-50">
            <div className="mx-auto max-w-7xl px-5 py-20 md:px-8 md:py-28">
                <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div className="max-w-2xl">
                        <h2 className="text-[2.1rem] md:text-[3rem]">
                            {content.title}
                        </h2>
                        {content.subtitle && (
                            <p className="mt-4 text-lg text-legno-700">
                                {content.subtitle}
                            </p>
                        )}
                    </div>
                    {content.button_label && (
                        <ButtonLink
                            href={products.index().url}
                            variant="secondary"
                            className="self-start md:self-auto"
                        >
                            {content.button_label}
                        </ButtonLink>
                    )}
                </div>
                <div className="mt-12">
                    <ProductGrid products={list} />
                </div>
            </div>
        </section>
    );
}
