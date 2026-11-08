import { Link } from '@inertiajs/react';
import { ButtonLink } from '@/components/public/button-link';
import { CatalogToolbar } from '@/components/public/catalog-toolbar';
import { CategoryPills } from '@/components/public/category-pills';
import { Pagination } from '@/components/public/pagination';
import { ProductGrid } from '@/components/public/product-grid';
import { Seo } from '@/components/public/seo';
import { useTranslations } from '@/lib/i18n';
import { contact } from '@/routes';
import products from '@/routes/products';
import type { CategoryData, Paginated, ProductCard } from '@/types/catalog';

type Props = {
    intro: {
        title: string;
        text?: string | null;
        image?: string | null;
    } | null;
    categories: CategoryData[];
    currentCategory: CategoryData | null;
    filters: { q: string; ordina: string };
    products: Paginated<ProductCard>;
    resultsLabel: string;
};

// F-CAT-01→09 : catalogue (et pages de catégorie). Le layout public est ajouté par app.tsx.
export default function Catalog({
    intro,
    categories,
    currentCategory,
    filters,
    products: list,
    resultsLabel,
}: Props) {
    const t = useTranslations();
    const catalogUrl = products.index().url;
    const basePath = currentCategory ? currentCategory.url : catalogUrl;
    const title = currentCategory?.name ?? intro?.title ?? t('nav.products');
    const description = currentCategory?.description ?? intro?.text ?? null;
    const keepQuery = filters.q ? `?q=${encodeURIComponent(filters.q)}` : '';

    return (
        <>
            <Seo title={title} description={description} />

            <section className="border-b border-crema-200 bg-crema-100">
                <div className="mx-auto max-w-7xl px-5 pt-14 pb-10 md:px-8 md:pt-20">
                    {currentCategory && (
                        <Link
                            href={catalogUrl}
                            className="font-display text-legno-700 hover:text-legno-900"
                        >
                            {t('nav.products')}
                        </Link>
                    )}
                    <h1 className="mt-2 max-w-3xl text-[2.6rem] md:text-[4rem]">
                        {title}
                    </h1>
                    {description && (
                        <p className="mt-5 max-w-2xl text-lg text-legno-700">
                            {description}
                        </p>
                    )}
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-5 py-10 md:px-8 md:py-14">
                <div className="flex flex-col gap-6">
                    <CategoryPills
                        categories={categories}
                        current={currentCategory?.slug ?? null}
                        query={keepQuery}
                    />
                    <CatalogToolbar
                        key={basePath}
                        basePath={basePath}
                        q={filters.q}
                        ordina={filters.ordina}
                    />
                    <p
                        className="font-display text-sm text-legno-700"
                        aria-live="polite"
                    >
                        {list.total > 0 && resultsLabel}
                    </p>
                </div>

                <div className="mt-6">
                    {list.data.length > 0 ? (
                        <ProductGrid products={list.data} />
                    ) : (
                        <div className="rounded-[var(--radius-card)] bg-crema-100 px-6 py-16 md:px-12">
                            <h2 className="text-3xl">
                                {t('catalog.empty_title')}
                            </h2>
                            <p className="mt-3 max-w-lg text-legno-700">
                                {t('catalog.empty_text')}
                            </p>
                            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                <ButtonLink href={catalogUrl}>
                                    {t('catalog.clear_filters')}
                                </ButtonLink>
                                <ButtonLink
                                    href={contact().url}
                                    variant="secondary"
                                >
                                    {t('actions.contact_us')}
                                </ButtonLink>
                            </div>
                        </div>
                    )}
                </div>

                <Pagination meta={list} />
            </section>
        </>
    );
}
