import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Star } from 'lucide-react';
import { useState } from 'react';
import { SortableList } from '@/components/admin/sortable-list';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import productRoutes from '@/routes/admin/products';
import unitRoutes from '@/routes/admin/units';

type Row = {
    id: number;
    name: string;
    category: string | null;
    price_label: string | null;
    availability: string;
    is_active: boolean;
    is_featured: boolean;
    thumbnail: string | null;
};
type Filters = { q?: string; category?: string; status?: string };
type Props = {
    products: Row[];
    categories: { id: number; name: string }[];
    filters: Filters;
};

// F-APROD-01/04/05 : liste, filtres, publication, ordre (désactivé quand un filtre est actif)
export default function ProductsIndex({
    products,
    categories,
    filters,
}: Props) {
    const t = useTranslations();
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const [q, setQ] = useState(filters.q ?? '');
    const filtered = Boolean(filters.q || filters.category || filters.status);

    const applyFilters = (next: Partial<Filters>) =>
        router.get(
            productRoutes.index().url,
            { ...filters, ...next },
            { preserveState: true, replace: true },
        );

    return (
        <>
            <Head title={t('products.title')} />
            <div className="flex flex-col gap-6 p-4 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold">
                        {t('products.title')}
                    </h1>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="secondary" asChild>
                            <Link href={unitRoutes.index()}>
                                {t('units.title')}
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={productRoutes.create()}>
                                <Plus className="size-4" /> {t('products.new')}
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="flex flex-wrap gap-3">
                    <input
                        value={q}
                        translate="no"
                        placeholder={t('catalog.search')}
                        aria-label={t('catalog.search')}
                        onChange={(e) => setQ(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                applyFilters({ q: q || undefined });
                            }
                        }}
                        className="h-9 w-56 rounded-md border border-input bg-transparent px-3 text-sm"
                    />
                    <select
                        value={filters.category ?? ''}
                        aria-label={t('products.filter_category')}
                        onChange={(e) =>
                            applyFilters({
                                category: e.target.value || undefined,
                            })
                        }
                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">
                            {t('products.filter_category')}: {t('catalog.all')}
                        </option>
                        {categories.map((c) => (
                            <option key={c.id} value={c.id} translate="no">
                                {c.name}
                            </option>
                        ))}
                    </select>
                    <select
                        value={filters.status ?? ''}
                        aria-label={t('products.filter_status')}
                        onChange={(e) =>
                            applyFilters({
                                status: e.target.value || undefined,
                            })
                        }
                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">
                            {t('products.filter_status')}: {t('catalog.all')}
                        </option>
                        <option value="active">{t('catalog.active')}</option>
                        <option value="inactive">
                            {t('catalog.inactive')}
                        </option>
                    </select>
                </div>

                {!filtered && (
                    <p className="text-sm text-muted-foreground">
                        {t('catalog.order_hint')}
                    </p>
                )}
                <InputError message={errors.product} />

                {products.length === 0 ? (
                    <p className="text-muted-foreground">
                        {t('catalog.empty')}
                    </p>
                ) : (
                    <SortableList
                        items={products}
                        disabled={filtered}
                        onReorder={(ids) =>
                            router.post(
                                productRoutes.reorder().url,
                                { ids },
                                { preserveScroll: true },
                            )
                        }
                        renderItem={(p, handle) => (
                            <div className="mb-2 flex flex-wrap items-center gap-3 rounded-lg border bg-background p-3">
                                {handle}
                                {p.thumbnail ? (
                                    <img
                                        src={p.thumbnail}
                                        alt=""
                                        className="size-14 shrink-0 rounded object-cover"
                                    />
                                ) : (
                                    <div className="size-14 shrink-0 rounded bg-muted" />
                                )}
                                <div className="min-w-0 flex-1">
                                    <p className="flex items-center gap-1 truncate font-medium">
                                        {p.is_featured && (
                                            <Star className="size-4 shrink-0 fill-amber-500 text-amber-500" />
                                        )}{' '}
                                        <span translate="no">{p.name}</span>
                                    </p>
                                    <p className="truncate text-sm text-muted-foreground">
                                        <span translate="no">
                                            {p.category ??
                                                t('products.no_category')}
                                        </span>{' '}
                                        —{' '}
                                        {p.price_label ??
                                            t('products.price_on_request')}{' '}
                                        — {p.availability}
                                    </p>
                                </div>
                                <Button
                                    variant={
                                        p.is_active ? 'secondary' : 'default'
                                    }
                                    size="sm"
                                    onClick={() =>
                                        router.visit(
                                            productRoutes.toggle(p.id),
                                            {
                                                preserveScroll: true,
                                            },
                                        )
                                    }
                                >
                                    {p.is_active
                                        ? t('catalog.unpublish')
                                        : t('catalog.publish')}
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    asChild
                                    aria-label={t('actions.edit')}
                                >
                                    <Link href={productRoutes.edit(p.id)}>
                                        <Pencil className="size-4" />
                                    </Link>
                                </Button>
                            </div>
                        )}
                    />
                )}
            </div>
        </>
    );
}
