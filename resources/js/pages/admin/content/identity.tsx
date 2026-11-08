import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { TextareaField } from '@/components/admin/fields';
import { ImageField } from '@/components/admin/image-field';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import contentRoutes from '@/routes/admin/content';
import identityRoutes from '@/routes/admin/identity';

type Key = 'logo_light' | 'logo_dark' | 'favicon' | 'default_share_image';
type Props = {
    previews: Record<Key, string | null>;
    texts: {
        footer_description: string | null;
        delivery_summary: string | null;
    };
};

const IMAGES: [Key, string, string][] = [
    ['logo_light', 'identity.logo_light', 'identity.logo_hint'],
    ['logo_dark', 'identity.logo_dark', 'identity.logo_hint'],
    ['favicon', 'identity.favicon', 'identity.favicon_hint'],
    ['default_share_image', 'identity.share_image', 'identity.share_hint'],
];

// F-ACONT-05 (sprint 7) : logos, favicon, image de partage, textes généraux
export default function Identity({ previews, texts }: Props) {
    const t = useTranslations();
    const form = useForm({
        logo_light: null as File | null,
        logo_dark: null as File | null,
        favicon: null as File | null,
        default_share_image: null as File | null,
        remove: [] as Key[],
        footer_description: texts.footer_description ?? '',
        delivery_summary: texts.delivery_summary ?? '',
    });

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.submit(identityRoutes.update(), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () =>
                form.reset(
                    'logo_light',
                    'logo_dark',
                    'favicon',
                    'default_share_image',
                    'remove',
                ),
        });
    };

    return (
        <>
            <Head title={t('identity.title')} />
            <form
                onSubmit={submit}
                className="flex max-w-3xl flex-col gap-8 p-4 md:p-8"
            >
                <h1 className="text-2xl font-semibold">
                    {t('identity.title')}
                </h1>

                <div className="grid gap-8 sm:grid-cols-2">
                    {IMAGES.map(([key, labelKey, hintKey]) => (
                        <ImageField
                            key={key}
                            id={key}
                            label={t(labelKey)}
                            hint={t(hintKey)}
                            preview={previews[key]}
                            error={form.errors[key]}
                            onFile={(file) => form.setData(key, file)}
                            remove={form.data.remove.includes(key)}
                            onRemove={(r) =>
                                form.setData(
                                    'remove',
                                    r
                                        ? [...form.data.remove, key]
                                        : form.data.remove.filter(
                                              (k) => k !== key,
                                          ),
                                )
                            }
                        />
                    ))}
                </div>

                <TextareaField
                    id="footer_description"
                    rows={3}
                    label={t('identity.footer_description')}
                    value={form.data.footer_description}
                    onChange={(e) =>
                        form.setData('footer_description', e.target.value)
                    }
                    error={form.errors.footer_description}
                />
                <TextareaField
                    id="delivery_summary"
                    rows={3}
                    label={t('identity.delivery_summary')}
                    hint={t('identity.delivery_summary_hint')}
                    value={form.data.delivery_summary}
                    onChange={(e) =>
                        form.setData('delivery_summary', e.target.value)
                    }
                    error={form.errors.delivery_summary}
                />

                <div className="flex gap-3">
                    <Button type="submit" disabled={form.processing}>
                        {t('actions.save')}
                    </Button>
                    <Button variant="secondary" asChild>
                        <Link href={contentRoutes.index()}>
                            {t('actions.back')}
                        </Link>
                    </Button>
                </div>
            </form>
        </>
    );
}
