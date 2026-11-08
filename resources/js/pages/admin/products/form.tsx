import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import type { FormEvent } from 'react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import {
    CheckboxField,
    SelectField,
    TextareaField,
    TextField,
} from '@/components/admin/fields';
import { ProductPhotos } from '@/components/admin/product-photos';
import type { AdminImage } from '@/components/admin/product-photos';
import { RichTextEditor } from '@/components/admin/rich-text-editor';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import productRoutes from '@/routes/admin/products';
import publicProducts from '@/routes/products';

type Option = { id: number; name: string };
type Product = {
    id: number;
    name: string;
    slug: string;
    category_id: number | null;
    short_description: string | null;
    description: string | null;
    price: string;
    sale_unit_id: number | null;
    wood_type: string | null;
    dimensions: string | null;
    packaging: string | null;
    availability: string;
    extra_info: string | null;
    is_featured: boolean;
    is_active: boolean;
    meta_title: string | null;
    meta_description: string | null;
    can_publish: boolean;
    images: AdminImage[];
};
type Props = {
    product: Product | null;
    categories: Option[];
    units: Option[];
    availabilities: { value: string; label: string }[];
    imageMaxMb: number;
};

// F-APROD-02/03/04/07 : fiche produit de l'admin (photos à droite une fois le produit créé)
export default function ProductForm({
    product,
    categories,
    units,
    availabilities,
    imageMaxMb,
}: Props) {
    const t = useTranslations();
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const form = useForm({
        name: product?.name ?? '',
        slug: product?.slug ?? '',
        category_id: product?.category_id ? String(product.category_id) : '',
        short_description: product?.short_description ?? '',
        description: product?.description ?? '',
        price: product?.price ?? '',
        sale_unit_id: product?.sale_unit_id ? String(product.sale_unit_id) : '',
        wood_type: product?.wood_type ?? '',
        dimensions: product?.dimensions ?? '',
        packaging: product?.packaging ?? '',
        availability: product?.availability ?? 'available',
        extra_info: product?.extra_info ?? '',
        is_featured: product?.is_featured ?? false,
        meta_title: product?.meta_title ?? '',
        meta_description: product?.meta_description ?? '',
    });

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (product) {
            form.submit(productRoutes.update(product.id), {
                preserveScroll: true,
            });
        } else {
            form.submit(productRoutes.store());
        }
    };

    type TextKey =
        | 'name'
        | 'slug'
        | 'price'
        | 'wood_type'
        | 'dimensions'
        | 'packaging'
        | 'meta_title';

    const text = (
        key: TextKey,
        label: string,
        extra: {
            required?: boolean;
            hint?: string;
            inputMode?: 'decimal';
        } = {},
    ) => (
        <TextField
            id={key}
            label={label}
            value={form.data[key]}
            onChange={(e) => form.setData(key, e.target.value)}
            error={form.errors[key]}
            {...extra}
        />
    );

    const title = product ? t('products.edit') : t('products.new');

    return (
        <>
            <Head title={title} />
            <div className="grid gap-10 p-4 md:p-8 xl:grid-cols-[1fr_24rem]">
                <form onSubmit={submit} className="flex min-w-0 flex-col gap-6">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h1 className="text-2xl font-semibold">{title}</h1>
                        {product && (
                            <span
                                className={
                                    product.is_active
                                        ? 'text-green-700'
                                        : 'text-muted-foreground'
                                }
                            >
                                {product.is_active
                                    ? t('catalog.active')
                                    : t('catalog.inactive')}
                            </span>
                        )}
                    </div>

                    {text('name', t('catalog.name'), { required: true })}
                    {text('slug', t('catalog.slug'), {
                        hint: t('catalog.slug_hint'),
                    })}
                    <SelectField
                        id="category_id"
                        label={t('products.category')}
                        placeholder={t('products.no_category')}
                        options={categories.map((c) => ({
                            value: c.id,
                            label: c.name,
                        }))}
                        value={form.data.category_id}
                        onChange={(e) =>
                            form.setData('category_id', e.target.value)
                        }
                        error={form.errors.category_id}
                    />
                    <TextareaField
                        id="short_description"
                        rows={3}
                        maxLength={300}
                        label={t('products.short_description')}
                        hint={t('products.short_description_hint')}
                        value={form.data.short_description}
                        onChange={(e) =>
                            form.setData('short_description', e.target.value)
                        }
                        error={form.errors.short_description}
                    />
                    <RichTextEditor
                        id="description"
                        label={t('products.description')}
                        value={form.data.description}
                        onChange={(html) => form.setData('description', html)}
                        error={form.errors.description}
                    />

                    <div className="grid gap-4 sm:grid-cols-2">
                        {text('price', t('products.price'), {
                            inputMode: 'decimal',
                            hint: t('products.price_hint'),
                        })}
                        <SelectField
                            id="sale_unit_id"
                            label={t('products.unit')}
                            placeholder="—"
                            options={units.map((u) => ({
                                value: u.id,
                                label: u.name,
                            }))}
                            value={form.data.sale_unit_id}
                            onChange={(e) =>
                                form.setData('sale_unit_id', e.target.value)
                            }
                            error={form.errors.sale_unit_id}
                        />
                    </div>

                    <fieldset className="grid gap-4 border-t pt-6">
                        <legend className="text-lg font-semibold">
                            {t('products.specs')}
                        </legend>
                        <p className="text-sm text-muted-foreground">
                            {t('products.specs_hint')}
                        </p>
                        <div className="grid gap-4 sm:grid-cols-2">
                            {text('wood_type', t('products.wood_type'))}
                            {text('dimensions', t('products.dimensions'))}
                            {text('packaging', t('products.packaging'))}
                            <SelectField
                                id="availability"
                                label={t('products.availability')}
                                options={availabilities.map((a) => ({
                                    value: a.value,
                                    label: a.label,
                                }))}
                                value={form.data.availability}
                                onChange={(e) =>
                                    form.setData('availability', e.target.value)
                                }
                                error={form.errors.availability}
                            />
                        </div>
                        <TextareaField
                            id="extra_info"
                            rows={3}
                            label={t('products.extra_info')}
                            value={form.data.extra_info}
                            onChange={(e) =>
                                form.setData('extra_info', e.target.value)
                            }
                            error={form.errors.extra_info}
                        />
                    </fieldset>

                    <CheckboxField
                        id="is_featured"
                        label={t('products.featured')}
                        checked={form.data.is_featured}
                        onChange={(v) => form.setData('is_featured', v)}
                    />

                    <fieldset className="grid gap-4 border-t pt-6">
                        <legend className="text-lg font-semibold">
                            {t('catalog.seo')}
                        </legend>
                        {text('meta_title', t('catalog.meta_title'))}
                        <TextareaField
                            id="meta_description"
                            rows={2}
                            label={t('catalog.meta_description')}
                            value={form.data.meta_description}
                            onChange={(e) =>
                                form.setData('meta_description', e.target.value)
                            }
                            error={form.errors.meta_description}
                        />
                    </fieldset>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" disabled={form.processing}>
                            {t('actions.save')}
                        </Button>
                        <Button variant="secondary" asChild>
                            <Link href={productRoutes.index()}>
                                {t('actions.back')}
                            </Link>
                        </Button>
                    </div>
                </form>

                {product && (
                    <aside className="flex min-w-0 flex-col gap-6">
                        <h2 className="text-lg font-semibold">
                            {t('products.photos')}
                        </h2>
                        <ProductPhotos
                            productId={product.id}
                            images={product.images}
                            maxMb={imageMaxMb}
                            errors={errors}
                        />

                        <div className="grid gap-3 border-t pt-6">
                            <InputError message={errors.product} />
                            {/* F-APROD-08 (sprint 5) : aperçu sur le site, même non publié (admin connecté) */}
                            <Button type="button" variant="outline" asChild>
                                <a
                                    href={publicProducts.show(product.slug).url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <ExternalLink className="size-4" />{' '}
                                    {t('products.view_on_site')}
                                </a>
                            </Button>
                            <Button
                                type="button"
                                variant={
                                    product.is_active ? 'secondary' : 'default'
                                }
                                disabled={
                                    !product.is_active && !product.can_publish
                                }
                                onClick={() =>
                                    router.visit(
                                        productRoutes.toggle(product.id),
                                        { preserveScroll: true },
                                    )
                                }
                            >
                                {product.is_active
                                    ? t('catalog.unpublish')
                                    : t('catalog.publish')}
                            </Button>
                            {!product.is_active && !product.can_publish && (
                                <p className="text-sm text-muted-foreground">
                                    {t('products.cannot_publish')}
                                </p>
                            )}
                            <ConfirmDialog
                                title={t('actions.delete')}
                                trigger={
                                    <Button type="button" variant="destructive">
                                        {t('actions.delete')}
                                    </Button>
                                }
                                onConfirm={() =>
                                    router.visit(
                                        productRoutes.destroy(product.id),
                                    )
                                }
                            />
                        </div>
                    </aside>
                )}
            </div>
        </>
    );
}
