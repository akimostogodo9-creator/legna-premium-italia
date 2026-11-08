import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { TextareaField, TextField } from '@/components/admin/fields';
import { OrderTemplatePreview } from '@/components/admin/order-template-preview';
import { TemplateEditor } from '@/components/admin/template-editor';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import settingsRoutes from '@/routes/admin/settings';

type Values = Record<string, string | null>;
type Props = {
    values: Values;
    variables: string[];
    sample: Record<string, string>;
};

const TEMPLATES = [
    ['order', 'settings.template_order'],
    ['availability', 'settings.template_availability'],
    ['general', 'settings.template_general'],
] as const;

// F-ASET-02/03/04/07 (sprint 6) : contacts, WhatsApp et modèles du message « Ordina » avec aperçu
export default function SettingsIndex({ values, variables, sample }: Props) {
    const t = useTranslations();
    const v = (key: string) => values[key] ?? '';
    const form = useForm({
        order_email: v('order_email'),
        contact_email: v('contact_email'),
        whatsapp_number: v('whatsapp_number'),
        whatsapp_message: v('whatsapp_message'),
        whatsapp_product_message: v('whatsapp_product_message'),
        order_subject_template: v('order_subject_template'),
        order_body_template: v('order_body_template'),
        availability_subject_template: v('availability_subject_template'),
        availability_body_template: v('availability_body_template'),
        general_subject_template: v('general_subject_template'),
        general_body_template: v('general_body_template'),
    });
    type Key = keyof typeof form.data;

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.submit(settingsRoutes.order(), { preserveScroll: true });
    };

    const field = (
        key: Key,
        label: string,
        extra: { type?: string; hint?: string; inputMode?: 'tel' } = {},
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

    return (
        <>
            <Head title={t('settings.title')} />
            <form
                onSubmit={submit}
                className="flex max-w-5xl flex-col gap-10 p-4 md:p-8"
            >
                <h1 className="text-2xl font-semibold">
                    {t('settings.title')}
                </h1>

                <section className="grid gap-5">
                    <h2 className="text-lg font-semibold">
                        {t('settings.contacts')}
                    </h2>
                    {field('order_email', t('settings.order_email'), {
                        type: 'email',
                        hint: t('settings.order_email_hint'),
                    })}
                    {field('contact_email', t('settings.contact_email'), {
                        type: 'email',
                    })}
                    {field('whatsapp_number', t('settings.whatsapp_number'), {
                        inputMode: 'tel',
                        hint: t('settings.whatsapp_hint'),
                    })}
                    <TextareaField
                        id="whatsapp_message"
                        rows={2}
                        label={t('settings.whatsapp_message')}
                        value={form.data.whatsapp_message}
                        onChange={(e) =>
                            form.setData('whatsapp_message', e.target.value)
                        }
                        error={form.errors.whatsapp_message}
                    />
                    <TemplateEditor
                        id="whatsapp_product_message"
                        rows={2}
                        label={t('settings.whatsapp_product_message')}
                        variables={variables}
                        value={form.data.whatsapp_product_message}
                        onChange={(val) =>
                            form.setData('whatsapp_product_message', val)
                        }
                        error={form.errors.whatsapp_product_message}
                    />
                </section>

                <section className="grid gap-10 border-t pt-8">
                    <h2 className="text-lg font-semibold">
                        {t('settings.templates')}
                    </h2>
                    {TEMPLATES.map(([kind, labelKey]) => {
                        const subjectKey: Key = `${kind}_subject_template`;
                        const bodyKey: Key = `${kind}_body_template`;

                        return (
                            <div
                                key={kind}
                                className="grid gap-4 lg:grid-cols-2"
                            >
                                <div className="grid min-w-0 gap-4">
                                    <h3 className="font-semibold">
                                        {t(labelKey)}
                                    </h3>
                                    {field(subjectKey, t('settings.subject'))}
                                    <TemplateEditor
                                        id={bodyKey}
                                        label={t('settings.body')}
                                        variables={variables}
                                        value={form.data[bodyKey]}
                                        onChange={(val) =>
                                            form.setData(bodyKey, val)
                                        }
                                        error={form.errors[bodyKey]}
                                    />
                                </div>
                                <OrderTemplatePreview
                                    email={form.data.order_email}
                                    subject={form.data[subjectKey]}
                                    body={form.data[bodyKey]}
                                    sample={sample}
                                />
                            </div>
                        );
                    })}
                </section>

                <Button
                    type="submit"
                    className="self-start"
                    disabled={form.processing}
                >
                    {t('actions.save')}
                </Button>
            </form>
        </>
    );
}
