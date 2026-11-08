import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import {
    CheckboxField,
    TextareaField,
    TextField,
} from '@/components/admin/fields';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import categoryRoutes from '@/routes/admin/categories';

type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    meta_title: string | null;
    meta_description: string | null;
    is_active: boolean;
    image: string | null;
};

// F-ACAT-02 : création / modification d'une catégorie (avec image facultative)
export default function CategoryForm({
    category,
}: {
    category: Category | null;
}) {
    const t = useTranslations();
    const form = useForm({
        name: category?.name ?? '',
        slug: category?.slug ?? '',
        description: category?.description ?? '',
        meta_title: category?.meta_title ?? '',
        meta_description: category?.meta_description ?? '',
        is_active: category?.is_active ?? true,
        image: null as File | null,
        remove_image: false,
    });

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // POST dans les deux cas : un fichier ne s'envoie pas en PUT (§11)
        form.submit(
            category
                ? categoryRoutes.update(category.id)
                : categoryRoutes.store(),
            { forceFormData: true },
        );
    };

    const title = category ? t('categories.edit') : t('categories.new');

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={submit}
                className="flex max-w-2xl flex-col gap-6 p-4 md:p-8"
            >
                <h1 className="text-2xl font-semibold">{title}</h1>

                <TextField
                    id="name"
                    label={t('catalog.name')}
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    error={form.errors.name}
                    required
                />
                <TextField
                    id="slug"
                    label={t('catalog.slug')}
                    hint={t('catalog.slug_hint')}
                    value={form.data.slug}
                    onChange={(e) => form.setData('slug', e.target.value)}
                    error={form.errors.slug}
                />
                <TextareaField
                    id="description"
                    label={t('catalog.description')}
                    value={form.data.description}
                    onChange={(e) =>
                        form.setData('description', e.target.value)
                    }
                    error={form.errors.description}
                />

                <div className="grid gap-2">
                    <label htmlFor="image" className="text-sm font-medium">
                        {t('catalog.image')}
                    </label>
                    {category?.image && !form.data.remove_image && (
                        <img
                            src={category.image}
                            alt=""
                            className="h-32 w-48 rounded object-cover"
                        />
                    )}
                    <input
                        id="image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        className="text-sm"
                        onChange={(e) =>
                            form.setData('image', e.target.files?.[0] ?? null)
                        }
                    />
                    {category?.image && (
                        <CheckboxField
                            id="remove_image"
                            label={t('catalog.remove_image')}
                            checked={form.data.remove_image}
                            onChange={(v) => form.setData('remove_image', v)}
                        />
                    )}
                    <InputError message={form.errors.image} />
                </div>

                <CheckboxField
                    id="is_active"
                    label={t('catalog.active')}
                    checked={form.data.is_active}
                    onChange={(v) => form.setData('is_active', v)}
                />

                <fieldset className="grid gap-4 border-t pt-6">
                    <legend className="text-lg font-semibold">
                        {t('catalog.seo')}
                    </legend>
                    <TextField
                        id="meta_title"
                        label={t('catalog.meta_title')}
                        value={form.data.meta_title}
                        onChange={(e) =>
                            form.setData('meta_title', e.target.value)
                        }
                        error={form.errors.meta_title}
                    />
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

                <div className="flex gap-3">
                    <Button type="submit" disabled={form.processing}>
                        {t('actions.save')}
                    </Button>
                    <Button variant="secondary" asChild>
                        <Link href={categoryRoutes.index()}>
                            {t('actions.cancel')}
                        </Link>
                    </Button>
                </div>
            </form>
        </>
    );
}
