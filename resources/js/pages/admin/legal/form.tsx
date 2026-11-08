import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import {
    CheckboxField,
    TextareaField,
    TextField,
} from '@/components/admin/fields';
import { RichTextEditor } from '@/components/admin/rich-text-editor';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import legalAdmin from '@/routes/admin/legal';

type Page = {
    id: number;
    slug: string;
    title: string;
    content: string | null;
    is_published: boolean;
    meta_description: string | null;
};

// F-ALEG-01 (sprint 8) : texte d'une page légale — fourni ou relu par un professionnel
export default function LegalForm({ page }: { page: Page }) {
    const t = useTranslations();
    const form = useForm({
        title: page.title,
        content: page.content ?? '',
        meta_description: page.meta_description ?? '',
        is_published: page.is_published,
    });

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.submit(legalAdmin.update(page.id));
    };

    return (
        <>
            <Head title={page.title} />
            <form
                onSubmit={submit}
                className="flex max-w-4xl flex-col gap-6 p-4 md:p-8"
            >
                <h1 className="text-2xl font-semibold" translate="no">
                    {page.title}
                </h1>
                <p className="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    {t('legal.intro')}
                </p>
                <TextField
                    id="title"
                    label={t('legal.page_title')}
                    value={form.data.title}
                    onChange={(e) => form.setData('title', e.target.value)}
                    error={form.errors.title}
                    required
                />
                <RichTextEditor
                    id="content"
                    label={t('legal.content')}
                    value={form.data.content}
                    onChange={(html) => form.setData('content', html)}
                    error={form.errors.content}
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
                <CheckboxField
                    id="is_published"
                    label={t('legal.published')}
                    checked={form.data.is_published}
                    onChange={(v) => form.setData('is_published', v)}
                    error={form.errors.is_published}
                />
                <div className="flex gap-3">
                    <Button type="submit" disabled={form.processing}>
                        {t('actions.save')}
                    </Button>
                    <Button variant="secondary" asChild>
                        <Link href={legalAdmin.index()}>
                            {t('actions.cancel')}
                        </Link>
                    </Button>
                </div>
            </form>
        </>
    );
}
