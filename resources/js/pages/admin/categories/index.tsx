import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import { SortableList } from '@/components/admin/sortable-list';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import categoryRoutes from '@/routes/admin/categories';

type Category = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    products_count: number;
    image: string | null;
};

// F-ACAT-01 : liste des catégories, ordre par glisser-déposer
export default function CategoriesIndex({
    categories,
}: {
    categories: Category[];
}) {
    const t = useTranslations();
    const { errors } = usePage<{ errors: Record<string, string> }>().props;

    return (
        <>
            <Head title={t('categories.title')} />
            <div className="flex flex-col gap-6 p-4 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold">
                        {t('categories.title')}
                    </h1>
                    <Button asChild>
                        <Link href={categoryRoutes.create()}>
                            <Plus className="size-4" /> {t('categories.new')}
                        </Link>
                    </Button>
                </div>
                <p className="text-sm text-muted-foreground">
                    {t('catalog.order_hint')}
                </p>
                <InputError message={errors.category} />

                {categories.length === 0 ? (
                    <p className="text-muted-foreground">
                        {t('catalog.empty')}
                    </p>
                ) : (
                    <SortableList
                        items={categories}
                        onReorder={(ids) =>
                            router.post(
                                categoryRoutes.reorder().url,
                                { ids },
                                { preserveScroll: true },
                            )
                        }
                        renderItem={(category, handle) => (
                            <div className="mb-2 flex items-center gap-3 rounded-lg border bg-background p-3">
                                {handle}
                                {category.image ? (
                                    <img
                                        src={category.image}
                                        alt=""
                                        className="size-12 rounded object-cover"
                                    />
                                ) : (
                                    <div className="size-12 rounded bg-muted" />
                                )}
                                <div className="min-w-0 flex-1">
                                    <p
                                        className="truncate font-medium"
                                        translate="no"
                                    >
                                        {category.name}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {category.is_active
                                            ? t('catalog.active')
                                            : t('catalog.inactive')}{' '}
                                        — {t('categories.products_count')}:{' '}
                                        {category.products_count}
                                    </p>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    asChild
                                    aria-label={t('actions.edit')}
                                >
                                    <Link
                                        href={categoryRoutes.edit(category.id)}
                                    >
                                        <Pencil className="size-4" />
                                    </Link>
                                </Button>
                                <ConfirmDialog
                                    title={t('actions.delete')}
                                    trigger={
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={t('actions.delete')}
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    }
                                    onConfirm={() =>
                                        router.visit(
                                            categoryRoutes.destroy(category.id),
                                            { preserveScroll: true },
                                        )
                                    }
                                />
                            </div>
                        )}
                    />
                )}
            </div>
        </>
    );
}
