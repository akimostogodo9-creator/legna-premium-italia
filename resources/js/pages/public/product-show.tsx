import { Link } from '@inertiajs/react';
import { Truck } from 'lucide-react';
import { AvailabilityBadge } from '@/components/public/availability-badge';
import { Breadcrumb } from '@/components/public/breadcrumb';
import { ButtonLink } from '@/components/public/button-link';
import { Price } from '@/components/public/price';
import { ProductGallery } from '@/components/public/product-gallery';
import { ProductGrid } from '@/components/public/product-grid';
import { ProductSpecs } from '@/components/public/product-specs';
import { RichText } from '@/components/public/rich-text';
import { Seo } from '@/components/public/seo';
import { useTranslations } from '@/lib/i18n';
import { contact, delivery, home } from '@/routes';
import products from '@/routes/products';
import type { ProductCard, ProductDetail } from '@/types/catalog';

type Props = {
    product: ProductDetail;
    preview: boolean;
    similar: ProductCard[];
    showIndicativeNotice: boolean;
    deliverySummary: string | null;
};

// F-PROD-01→09 — le bouton « Ordina » et la barre mobile arrivent au sprint 6 (emplacement marqué)
export default function ProductShow({
    product,
    preview,
    similar,
    showIndicativeNotice,
    deliverySummary,
}: Props) {
    const t = useTranslations();
    const categoryUrl = product.category
        ? products.category(product.category.slug).url
        : null;

    const crumbs = [
        { label: t('nav.home'), href: home().url },
        { label: t('nav.products'), href: products.index().url },
        ...(product.category && categoryUrl
            ? [{ label: product.category.name, href: categoryUrl }]
            : []),
        { label: product.name },
    ];

    return (
        <>
            <Seo
                title={product.meta_title ?? product.name}
                description={product.meta_description}
                image={product.image?.src}
            />

            {preview && (
                <p
                    role="status"
                    className="bg-brace-500 px-5 py-3 text-center font-display font-medium text-legno-900"
                >
                    {t('product.preview')}
                </p>
            )}

            <div className="mx-auto max-w-7xl px-5 pt-8 md:px-8">
                <Breadcrumb items={crumbs} />
            </div>

            <section className="mx-auto grid max-w-7xl gap-10 px-5 pt-6 pb-16 md:px-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16">
                {/* key : la galerie repart de la 1re photo quand on passe à un autre produit */}
                <ProductGallery key={product.id} images={product.images} />

                <div className="min-w-0 lg:sticky lg:top-28 lg:self-start">
                    <div className="flex flex-wrap items-center gap-2">
                        {product.category && categoryUrl && (
                            <Link
                                href={categoryUrl}
                                className="font-display text-legno-700 hover:text-legno-900"
                            >
                                {product.category.name}
                            </Link>
                        )}
                        <AvailabilityBadge
                            availability={product.availability}
                        />
                    </div>
                    <h1 className="mt-3 text-[2.4rem] md:text-[3.2rem]">
                        {product.name}
                    </h1>
                    {product.short_description && (
                        <p className="mt-4 text-lg text-legno-900/85">
                            {product.short_description}
                        </p>
                    )}

                    <div className="mt-8 border-t border-crema-200 pt-6">
                        <Price
                            label={product.price_label}
                            unit={product.unit}
                            size="detail"
                        />
                        {product.price_label && (
                            <p className="mt-1 text-sm text-legno-700">
                                {t('product.vat_included')}
                                {showIndicativeNotice &&
                                    ` — ${t('product.indicative_price')}`}
                            </p>
                        )}
                    </div>

                    {/* Sprint 6 : <OrderButton product={product} /> + WhatsApp + barre mobile */}
                    <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                        <ButtonLink href={contact().url} variant="secondary">
                            {t('actions.contact_us')}
                        </ButtonLink>
                    </div>

                    {deliverySummary && (
                        <div className="mt-8 flex gap-4 rounded-[var(--radius-card)] bg-crema-100 p-5">
                            <Truck
                                className="mt-1 size-6 shrink-0 text-bosco-700"
                                aria-hidden="true"
                            />
                            <div>
                                <h2 className="text-lg">
                                    {t('product.delivery')}
                                </h2>
                                <p className="mt-1 text-[0.98rem] whitespace-pre-line text-legno-900/85">
                                    {deliverySummary}
                                </p>
                                <Link
                                    href={delivery()}
                                    className="mt-2 inline-block font-display font-medium text-legno-700 underline underline-offset-4"
                                >
                                    {t('product.delivery_link')}
                                </Link>
                            </div>
                        </div>
                    )}
                </div>
            </section>

            <section className="mx-auto grid max-w-7xl gap-14 px-5 pb-20 md:px-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16">
                {product.description ? (
                    <div className="min-w-0">
                        <h2 className="text-2xl">{t('product.description')}</h2>
                        <RichText
                            html={product.description}
                            className="mt-5 text-lg text-legno-900/90"
                        />
                    </div>
                ) : (
                    <div />
                )}
                <ProductSpecs specs={product.specs} />
            </section>

            {similar.length > 0 && (
                <section className="bg-crema-100">
                    <div className="mx-auto max-w-7xl px-5 py-20 md:px-8">
                        <h2 className="text-[2rem] md:text-[2.6rem]">
                            {t('product.similar')}
                        </h2>
                        <div className="mt-10">
                            <ProductGrid products={similar} />
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}
