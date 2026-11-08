import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { TextareaField, TextField } from '@/components/admin/fields';
import { ImageField } from '@/components/admin/image-field';
import { ItemsEditor } from '@/components/admin/items-editor';
import type { Item } from '@/components/admin/items-editor';
import { RichTextEditor } from '@/components/admin/rich-text-editor';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import contentRoutes from '@/routes/admin/content';

type FieldType = 'text' | 'textarea' | 'richtext' | 'image' | 'items';
type Props = {
    section: {
        id: number;
        page: string;
        key: string;
        label: string;
        is_visible: boolean;
    };
    fields: Record<string, FieldType>;
    required: string[];
    content: Record<string, unknown>;
    previews: Record<string, string | null>;
    icons: string[];
};

/** Valeurs possibles d'un champ généré : texte, case, fichier, liste d'images à retirer, blocs */
type FieldValue = string | boolean | File | null | string[] | Item[];
type FormValues = Record<string, FieldValue>;

/** F-ACONT (sprint 7) : formulaire GÉNÉRÉ à partir du format de la section (registre — sprint 1). */
export default function ContentEdit({
    section,
    fields,
    required,
    content,
    previews,
    icons,
}: Props) {
    const t = useTranslations();

    const initial: FormValues = { remove_images: [] };

    for (const [name, type] of Object.entries(fields)) {
        if (type === 'image') {
            initial[name] = null;
            initial[`${name}_provisional`] = Boolean(
                content[`${name}_provisional`],
            );
        } else if (type === 'items') {
            initial[name] = (content[name] as Item[] | undefined) ?? [];
        } else {
            initial[name] = (content[name] as string | null) ?? '';
        }
    }

    const form = useForm<FormValues>(initial);
    const data = form.data;
    const errors = form.errors as Record<string, string>;
    const set = (name: string, value: FieldValue) =>
        form.setData((current) => ({ ...current, [name]: value }));

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // POST + FormData : les images s'envoient comme fichiers
        form.submit(contentRoutes.update(section.id), {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const label = (name: string) =>
        `${t(`content.fields.${name}`)}${required.includes(name) ? ' *' : ''}`;
    const removeList = (data.remove_images as string[]) ?? [];
    const text = (name: string) => {
        const value = data[name];

        return typeof value === 'string' ? value : '';
    };

    return (
        <>
            <Head title={section.label} />
            <form
                onSubmit={submit}
                className="flex max-w-3xl flex-col gap-6 p-4 md:p-8"
            >
                <div>
                    <p className="text-sm text-muted-foreground">
                        {t(`content.pages.${section.page}`)}
                    </p>
                    <h1 className="text-2xl font-semibold">{section.label}</h1>
                </div>

                {Object.entries(fields).map(([name, type]) => {
                    switch (type) {
                        case 'text':
                            return (
                                <TextField
                                    key={name}
                                    id={name}
                                    label={label(name)}
                                    value={text(name)}
                                    onChange={(e) => set(name, e.target.value)}
                                    error={errors[name]}
                                />
                            );
                        case 'textarea':
                            return (
                                <TextareaField
                                    key={name}
                                    id={name}
                                    label={label(name)}
                                    value={text(name)}
                                    onChange={(e) => set(name, e.target.value)}
                                    error={errors[name]}
                                />
                            );
                        case 'richtext':
                            return (
                                <RichTextEditor
                                    key={name}
                                    id={name}
                                    label={label(name)}
                                    value={text(name)}
                                    onChange={(html) => set(name, html)}
                                    error={errors[name]}
                                />
                            );
                        case 'image':
                            return (
                                <ImageField
                                    key={name}
                                    id={name}
                                    label={label(name)}
                                    preview={previews[name] ?? null}
                                    error={errors[name]}
                                    onFile={(file) => set(name, file)}
                                    remove={removeList.includes(name)}
                                    onRemove={(r) =>
                                        set(
                                            'remove_images',
                                            r
                                                ? [...removeList, name]
                                                : removeList.filter(
                                                      (n) => n !== name,
                                                  ),
                                        )
                                    }
                                    provisional={{
                                        checked: Boolean(
                                            data[`${name}_provisional`],
                                        ),
                                        onChange: (v) =>
                                            set(`${name}_provisional`, v),
                                    }}
                                />
                            );
                        case 'items':
                            return (
                                <ItemsEditor
                                    key={name}
                                    name={name}
                                    items={data[name] as Item[]}
                                    icons={icons}
                                    onChange={(items) => set(name, items)}
                                    errors={errors}
                                />
                            );
                        default:
                            return null;
                    }
                })}

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
