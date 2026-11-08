import { Link } from '@inertiajs/react';
import { useTranslations } from '@/lib/i18n';

type Item = { label: string; href?: string };

// F-PROD-01 : Home › Prodotti › Catégorie › Produit (données structurées au sprint 11)
export function Breadcrumb({ items }: { items: Item[] }) {
    const t = useTranslations();

    return (
        <nav
            aria-label={t('product.breadcrumb')}
            className="font-display text-sm text-legno-700"
        >
            <ol className="flex flex-wrap items-center gap-x-2 gap-y-1">
                {items.map((item, index) => (
                    <li key={item.label} className="flex items-center gap-2">
                        {index > 0 && <span aria-hidden="true">/</span>}
                        {item.href ? (
                            <Link
                                href={item.href}
                                className="hover:text-legno-900"
                            >
                                {item.label}
                            </Link>
                        ) : (
                            <span
                                aria-current="page"
                                className="text-legno-900"
                            >
                                {item.label}
                            </span>
                        )}
                    </li>
                ))}
            </ol>
        </nav>
    );
}
