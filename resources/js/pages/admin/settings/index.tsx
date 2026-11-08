import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import {
    CheckboxField,
    TextareaField,
    TextField,
} from '@/components/admin/fields';
import { OrderTemplatePreview } from '@/components/admin/order-template-preview';
import { TemplateEditor } from '@/components/admin/template-editor';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import settingsRoutes from '@/routes/admin/settings';

type Values = Record<string, string | null>;
type Props = {
    values: Values;
    company: Values;
    form: {
        contact_form_enabled?: boolean | null;
        contact_rate_limit_per_hour?: number | null;
        contact_retention_months?: number | null;
    };
    catalog: {
        show_indicative_price_notice?: boolean | null;
        products_per_page?: number | null;
        featured_count?: number | null;
        image_max_mb?: number | null;
        view_dedup_minutes?: number | null;
    };
    variables: string[];
    sample: Record<string, string>;
};

// Sprint 10 : champs numériques de la section « Catalogo » (valeur par défaut, minimum, maximum)
const CATALOG_NUMBERS = [
    ['products_per_page', 12, 3, 48],
    ['featured_count', 4, 1, 12],
    ['image_max_mb', 8, 1, 20],
    ['view_dedup_minutes', 30, 5, 1440],
] as const;

// Sprint 8 (F-ASET-01) : données de l'entreprise — formulaire séparé, enregistré à part
const COMPANY_FIELDS = [
    'company_name',
    'company_address',
    'company_vat',
    'company_tax_code',
    'company_rea',
    'company_phone',
    'company_hours',
    'delivery_area',
] as const;
type CompanyKey = (typeof COMPANY_FIELDS)[number];
const MULTILINE: readonly CompanyKey[] = [
    'company_address',
    'company_hours',
    'delivery_area',
];

const TEMPLATES = [
    ['order', 'settings.template_order'],
    ['availability', 'settings.template_availability'],
    ['general', 'settings.template_general'],
] as const;

// F-ASET-01 (azienda, sprint 8) et F-ASET-02/03/04/07 (sprint 6) : contacts, WhatsApp, modèles « Ordina »
export default function SettingsIndex({
    values,
    company: companyValues,
    form: formValues,
    catalog: catalogValues,
    variables,
    sample,
}: Props) {
    const t = useTranslations();
    const v = (key: string) => values[key] ?? '';
    const company = useForm(
        Object.fromEntries(
            COMPANY_FIELDS.map((k) => [k, companyValues[k] ?? '']),
        ) as Record<CompanyKey, string>,
    );
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

    // Sprint 9 (F-ASET-06) : formulaire de contact — troisième formulaire indépendant
    const contactForm = useForm({
        contact_form_enabled: formValues.contact_form_enabled ?? true,
        contact_rate_limit_per_hour: String(
            formValues.contact_rate_limit_per_hour ?? 5,
        ),
        contact_retention_months: String(
            formValues.contact_retention_months ?? 24,
        ),
    });

    // Sprint 10 (F-ASET-05) : catalogue, images et statistiques — quatrième formulaire indépendant
    const catalogForm = useForm({
        show_indicative_price_notice:
            catalogValues.show_indicative_price_notice ?? true,
        products_per_page: String(catalogValues.products_per_page ?? 12),
        featured_count: String(catalogValues.featured_count ?? 4),
        image_max_mb: String(catalogValues.image_max_mb ?? 8),
        view_dedup_minutes: String(catalogValues.view_dedup_minutes ?? 30),
    });

    const submitCatalog = (event: FormEvent) => {
        event.preventDefault();
        catalogForm.submit(settingsRoutes.catalog(), { preserveScroll: true });
    };

    useUnsavedChangesWarning(
        (form.isDirty && !form.processing) ||
            (company.isDirty && !company.processing) ||
            (contactForm.isDirty && !contactForm.processing) ||
            (catalogForm.isDirty && !catalogForm.processing),
    );

    const submitContactForm = (event: FormEvent) => {
        event.preventDefault();
        contactForm.submit(settingsRoutes.form(), { preserveScroll: true });
    };

    const submitCompany = (event: FormEvent) => {
        event.preventDefault();
        company.submit(settingsRoutes.company(), { preserveScroll: true });
    };

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
            <div className="flex max-w-5xl flex-col gap-12 p-4 md:p-8">
                <h1 className="text-2xl font-semibold">
                    {t('settings.title')}
                </h1>

                {/* Sprint 10 : sommaire de la page (4 formulaires indépendants) */}
                <nav
                    aria-label={t('settings.sections')}
                    className="flex flex-wrap gap-2 text-sm"
                >
                    {(
                        [
                            ['#azienda', t('settings.company')],
                            ['#modulo', t('settings.form')],
                            ['#catalogo', t('settings.catalog')],
                            ['#contatti', t('settings.contacts')],
                        ] as const
                    ).map(([href, label]) => (
                        <a
                            key={href}
                            href={href}
                            className="rounded-full border px-3 py-1 hover:bg-muted"
                        >
                            {label}
                        </a>
                    ))}
                </nav>

                <form
                    id="azienda"
                    onSubmit={submitCompany}
                    className="grid scroll-mt-6 gap-5"
                >
                    <h2 className="text-lg font-semibold">
                        {t('settings.company')}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        {t('settings.company_hint')}
                    </p>
                    {COMPANY_FIELDS.map((key) =>
                        MULTILINE.includes(key) ? (
                            <TextareaField
                                key={key}
                                id={key}
                                rows={2}
                                label={t(`settings.${key}`)}
                                value={company.data[key]}
                                onChange={(e) =>
                                    company.setData(key, e.target.value)
                                }
                                error={company.errors[key]}
                            />
                        ) : (
                            <TextField
                                key={key}
                                id={key}
                                label={t(`settings.${key}`)}
                                value={company.data[key]}
                                onChange={(e) =>
                                    company.setData(key, e.target.value)
                                }
                                error={company.errors[key]}
                            />
                        ),
                    )}
                    <Button
                        type="submit"
                        className="self-start"
                        disabled={company.processing}
                    >
                        {t('actions.save')}
                    </Button>
                </form>

                <form
                    id="modulo"
                    onSubmit={submitContactForm}
                    className="grid scroll-mt-6 gap-5 border-t pt-10"
                >
                    <h2 className="text-lg font-semibold">
                        {t('settings.form')}
                    </h2>
                    <CheckboxField
                        id="contact_form_enabled"
                        label={t('settings.contact_form_enabled')}
                        hint={t('settings.contact_form_hint')}
                        checked={contactForm.data.contact_form_enabled}
                        onChange={(v) =>
                            contactForm.setData('contact_form_enabled', v)
                        }
                    />
                    <div className="grid gap-5 sm:grid-cols-2">
                        <TextField
                            id="contact_rate_limit_per_hour"
                            type="number"
                            min={1}
                            max={50}
                            label={t('settings.contact_rate_limit_per_hour')}
                            value={contactForm.data.contact_rate_limit_per_hour}
                            onChange={(e) =>
                                contactForm.setData(
                                    'contact_rate_limit_per_hour',
                                    e.target.value,
                                )
                            }
                            error={
                                contactForm.errors.contact_rate_limit_per_hour
                            }
                        />
                        <TextField
                            id="contact_retention_months"
                            type="number"
                            min={1}
                            max={120}
                            label={t('settings.contact_retention_months')}
                            value={contactForm.data.contact_retention_months}
                            onChange={(e) =>
                                contactForm.setData(
                                    'contact_retention_months',
                                    e.target.value,
                                )
                            }
                            error={contactForm.errors.contact_retention_months}
                        />
                    </div>
                    <Button
                        type="submit"
                        className="self-start"
                        disabled={contactForm.processing}
                    >
                        {t('actions.save')}
                    </Button>
                </form>

                <form
                    id="catalogo"
                    onSubmit={submitCatalog}
                    className="grid scroll-mt-6 gap-5 border-t pt-10"
                >
                    <h2 className="text-lg font-semibold">
                        {t('settings.catalog')}
                    </h2>
                    <CheckboxField
                        id="show_indicative_price_notice"
                        label={t('settings.show_indicative_price_notice')}
                        checked={catalogForm.data.show_indicative_price_notice}
                        onChange={(v) =>
                            catalogForm.setData(
                                'show_indicative_price_notice',
                                v,
                            )
                        }
                    />
                    <div className="grid gap-5 sm:grid-cols-2">
                        {CATALOG_NUMBERS.map(([key, , min, max]) => (
                            <TextField
                                key={key}
                                id={key}
                                type="number"
                                min={min}
                                max={max}
                                label={t(`settings.${key}`)}
                                hint={
                                    key === 'image_max_mb'
                                        ? t('settings.image_max_hint')
                                        : undefined
                                }
                                value={catalogForm.data[key]}
                                onChange={(e) =>
                                    catalogForm.setData(key, e.target.value)
                                }
                                error={catalogForm.errors[key]}
                            />
                        ))}
                    </div>
                    <Button
                        type="submit"
                        className="self-start"
                        disabled={catalogForm.processing}
                    >
                        {t('actions.save')}
                    </Button>
                </form>

                <form
                    id="contatti"
                    onSubmit={submit}
                    className="flex scroll-mt-6 flex-col gap-10 border-t pt-10"
                >
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
                        {field(
                            'whatsapp_number',
                            t('settings.whatsapp_number'),
                            {
                                inputMode: 'tel',
                                hint: t('settings.whatsapp_hint'),
                            },
                        )}
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
                                        {field(
                                            subjectKey,
                                            t('settings.subject'),
                                        )}
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
            </div>
        </>
    );
}
