import { Link } from '@inertiajs/react';
import { useTranslations } from '@/lib/i18n';
import type { Paginated } from '@/types/catalog';

// F-CAT-07 : vrais liens (indexables), accessibles
export function Pagination({ meta }: { meta: Paginated<unknown> }) {
    const t = useTranslations();

    if (meta.last_page <= 1) {
        return null;
    }

    const base =
        'inline-flex min-h-11 min-w-11 items-center justify-center rounded-full px-3 font-display font-medium';

    return (
        <nav
            aria-label={t('catalog.pagination')}
            className="mt-14 flex flex-wrap items-center justify-center gap-2"
        >
            {meta.prev_url && (
                <Link
                    href={meta.prev_url}
                    className={`${base} text-legno-900 hover:bg-crema-100`}
                >
                    {t('catalog.previous')}
                </Link>
            )}
            {meta.pages.map(({ page, url }) => (
                <Link
                    key={page}
                    href={url}
                    aria-label={t('catalog.page', { page })}
                    aria-current={
                        page === meta.current_page ? 'page' : undefined
                    }
                    className={`${base} ${page === meta.current_page ? 'bg-legno-900 text-crema-50' : 'text-legno-900 hover:bg-crema-100'}`}
                >
                    {page}
                </Link>
            ))}
            {meta.next_url && (
                <Link
                    href={meta.next_url}
                    className={`${base} text-legno-900 hover:bg-crema-100`}
                >
                    {t('catalog.next')}
                </Link>
            )}
        </nav>
    );
}
