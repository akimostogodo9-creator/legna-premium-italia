import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useTranslations } from '@/lib/i18n';

type Props = { basePath: string; q: string; ordina: string };

const SORTS = [
    ['in-evidenza', 'catalog.sort_featured'],
    ['nome', 'catalog.sort_name'],
    ['prezzo-crescente', 'catalog.sort_price_asc'],
    ['prezzo-decrescente', 'catalog.sort_price_desc'],
] as const;

// F-CAT-04/06/09 : recherche (après 300 ms sans frapper) et tri ; l'état reste dans l'URL.
// Sprint 5 : le délai est géré dans le gestionnaire de saisie (pas dans un effet), plus simple et plus sûr.
export function CatalogToolbar({ basePath, q, ordina }: Props) {
    const t = useTranslations();
    const [search, setSearch] = useState(q);
    const timer = useRef<ReturnType<typeof setTimeout> | null>(null);

    // Nettoyage du délai si on quitte la page avant les 300 ms
    useEffect(
        () => () => {
            if (timer.current) {
                clearTimeout(timer.current);
            }
        },
        [],
    );

    const visit = (next: { q: string; ordina: string }) => {
        router.get(
            basePath,
            {
                ...(next.q ? { q: next.q } : {}),
                ...(next.ordina !== 'in-evidenza'
                    ? { ordina: next.ordina }
                    : {}),
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const onSearch = (value: string) => {
        setSearch(value);

        if (timer.current) {
            clearTimeout(timer.current);
        }

        timer.current = setTimeout(() => visit({ q: value, ordina }), 300);
    };

    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label className="relative block sm:w-80">
                <span className="sr-only">{t('catalog.search_label')}</span>
                <Search
                    className="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-legno-700"
                    aria-hidden="true"
                />
                <input
                    type="search"
                    value={search}
                    onChange={(e) => onSearch(e.target.value)}
                    placeholder={t('catalog.search')}
                    className="h-12 w-full rounded-full border border-crema-200 bg-crema-50 pr-4 pl-11 font-display text-legno-900 placeholder:text-legno-700/60 focus:border-legno-700 focus:outline-none"
                />
            </label>
            <label className="flex items-center gap-3 font-display text-legno-900">
                <span className="text-sm text-legno-700">
                    {t('catalog.sort')}
                </span>
                <select
                    value={ordina}
                    onChange={(e) =>
                        visit({ q: search, ordina: e.target.value })
                    }
                    className="h-12 rounded-full border border-crema-200 bg-crema-50 px-4 focus:border-legno-700 focus:outline-none"
                >
                    {SORTS.map(([value, key]) => (
                        <option key={value} value={value}>
                            {t(key)}
                        </option>
                    ))}
                </select>
            </label>
        </div>
    );
}
