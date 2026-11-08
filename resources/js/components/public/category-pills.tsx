import { Link } from '@inertiajs/react';
import { useTranslations } from '@/lib/i18n';
import products from '@/routes/products';
import type { CategoryData } from '@/types/catalog';

type Props = {
    categories: CategoryData[];
    current: string | null;
    query: string;
};

// F-CAT-03 : « Tutti » + catégories actives ; défilement horizontal sur téléphone
export function CategoryPills({ categories, current, query }: Props) {
    const t = useTranslations();

    if (categories.length === 0) {
        return null;
    }

    const pill = (href: string, label: string, active: boolean) => (
        <li key={href}>
            <Link
                href={href + query}
                preserveScroll
                aria-current={active ? 'page' : undefined}
                className={`inline-flex min-h-10 items-center rounded-full px-4 font-display text-[0.95rem] font-medium whitespace-nowrap transition-colors ${
                    active
                        ? 'bg-legno-900 text-crema-50'
                        : 'bg-crema-100 text-legno-900 hover:bg-crema-200'
                }`}
            >
                {label}
            </Link>
        </li>
    );

    return (
        <nav
            aria-label={t('catalog.categories')}
            className="-mx-5 overflow-x-auto px-5 md:mx-0 md:px-0"
        >
            <ul className="flex gap-2 pb-1">
                {pill(products.index().url, t('catalog.all'), current === null)}
                {categories.map((c) => pill(c.url, c.name, current === c.slug))}
            </ul>
        </nav>
    );
}
