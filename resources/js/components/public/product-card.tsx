import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useTranslations } from '@/lib/i18n';
import type { ProductCard as ProductCardData } from '@/types/catalog';
import { AvailabilityBadge } from './availability-badge';
import { GrowthRings } from './growth-rings';
import { Price } from './price';
import { ResponsiveImage } from './responsive-image';

type Props = {
    product: ProductCardData;
    /** Emplacement du bouton « Ordina » (branché au sprint 6) */
    actions?: ReactNode;
};

/**
 * F-CAT-05 / CDC §9.1-9.2 : photo 4:3, catégorie, disponibilité, nom, description courte,
 * prix + unité (ou « su richiesta »), « Scopri di più » + emplacement « Ordina ».
 * Toute la carte n'est pas un lien : les deux boutons doivent rester distincts.
 */
export function ProductCard({ product, actions }: Props) {
    const t = useTranslations();

    return (
        <article className="group flex flex-col overflow-hidden rounded-[var(--radius-card)] bg-crema-50 shadow-[var(--shadow-card)] transition-shadow duration-300 hover:shadow-[var(--shadow-card-hover)]">
            <Link
                href={product.url}
                className="relative block aspect-[4/3] overflow-hidden bg-legno-900"
                tabIndex={-1}
                aria-hidden="true"
            >
                {product.image ? (
                    <ResponsiveImage
                        image={product.image}
                        sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 100vw"
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                    />
                ) : (
                    <GrowthRings className="size-full text-brace-500/40" />
                )}
            </Link>

            <div className="flex flex-1 flex-col p-5 md:p-6">
                <div className="flex flex-wrap items-center gap-2">
                    {product.category && (
                        <span className="font-display text-sm text-legno-700">
                            {product.category.name}
                        </span>
                    )}
                    {product.availability.value !== 'available' && (
                        <AvailabilityBadge
                            availability={product.availability}
                        />
                    )}
                </div>

                <h3 className="mt-2 text-[1.45rem] text-legno-900">
                    <Link href={product.url} className="hover:text-legno-700">
                        {product.name}
                    </Link>
                </h3>

                {product.short_description && (
                    <p className="mt-2 line-clamp-3 text-[0.98rem] text-legno-900/80">
                        {product.short_description}
                    </p>
                )}

                <div className="mt-auto pt-5">
                    <Price label={product.price_label} unit={product.unit} />
                    <div className="mt-4 flex flex-wrap gap-2">
                        {actions}
                        <Link
                            href={product.url}
                            className="inline-flex min-h-11 items-center rounded-full border border-legno-900/25 px-5 font-display font-semibold text-legno-900 transition-colors hover:border-legno-900 hover:bg-crema-100"
                        >
                            {t('product.discover')}
                        </Link>
                    </div>
                </div>
            </div>
        </article>
    );
}
