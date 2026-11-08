import { Link, useForm, usePage } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { useTranslations } from '@/lib/i18n';
import contact from '@/routes/contact';
import type { PublicPageProps } from '@/types/site';

type Props = { subjects: { value: string; label: string }[] };

const input =
    'h-12 w-full rounded-[var(--radius-field)] border border-crema-200 bg-crema-50 px-4 font-display text-legno-900 focus:border-legno-700 focus:outline-none aria-[invalid=true]:border-red-700';

/**
 * F-CONT-03→06 (sprint 9) : renseignements uniquement (R-CONT-02), consentement obligatoire, champ piège.
 * Aucun champ quantité, produit ou adresse de livraison : ce n'est PAS un formulaire de commande.
 */
export function ContactForm({ subjects }: Props) {
    const t = useTranslations();
    const { site, flash } = usePage<PublicPageProps>().props;
    const form = useForm({
        name: '',
        email: '',
        phone: '',
        subject: subjects[0]?.value ?? 'products',
        message: '',
        privacy: false,
        website: '',
    });
    const privacyPage = site.legal_pages.find(
        (p) => p.url === '/privacy-policy',
    );
    type Field = keyof typeof form.data;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.submit(contact.store(), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const error = (field: Field) =>
        form.errors[field] ? (
            <p id={`${field}-error`} className="mt-1.5 text-sm text-red-800">
                {form.errors[field]}
            </p>
        ) : null;

    const label = (field: Field, text: string): ReactNode => (
        <label
            htmlFor={field}
            className="mb-1.5 block font-display font-medium"
        >
            {text}
        </label>
    );

    if (flash?.success) {
        return (
            <p
                role="status"
                className="rounded-[var(--radius-card)] bg-bosco-700 p-6 font-display text-lg text-crema-50"
            >
                {flash.success}
            </p>
        );
    }

    return (
        <form onSubmit={submit} noValidate className="grid gap-5">
            <div className="grid gap-5 sm:grid-cols-2">
                <div>
                    {label('name', t('contact.name'))}
                    <input
                        id="name"
                        autoComplete="name"
                        className={input}
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        aria-invalid={!!form.errors.name}
                        aria-describedby="name-error"
                        required
                    />
                    {error('name')}
                </div>
                <div>
                    {label('email', t('contact.email_field'))}
                    <input
                        id="email"
                        type="email"
                        autoComplete="email"
                        className={input}
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                        aria-invalid={!!form.errors.email}
                        aria-describedby="email-error"
                        required
                    />
                    {error('email')}
                </div>
                <div>
                    {label('phone', t('contact.phone_field'))}
                    <input
                        id="phone"
                        type="tel"
                        autoComplete="tel"
                        className={input}
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                        aria-invalid={!!form.errors.phone}
                        aria-describedby="phone-error"
                    />
                    {error('phone')}
                </div>
                <div>
                    {label('subject', t('contact.subject'))}
                    <select
                        id="subject"
                        className={input}
                        value={form.data.subject}
                        onChange={(e) =>
                            form.setData('subject', e.target.value)
                        }
                    >
                        {subjects.map((s) => (
                            <option key={s.value} value={s.value}>
                                {s.label}
                            </option>
                        ))}
                    </select>
                    {error('subject')}
                </div>
            </div>

            <div>
                {label('message', t('contact.message'))}
                <textarea
                    id="message"
                    rows={6}
                    className={`${input} h-auto py-3`}
                    value={form.data.message}
                    onChange={(e) => form.setData('message', e.target.value)}
                    aria-invalid={!!form.errors.message}
                    aria-describedby="message-hint message-error"
                    required
                />
                <p id="message-hint" className="mt-1.5 text-sm text-legno-700">
                    {t('contact.message_hint')}
                </p>
                {error('message')}
            </div>

            {/* Champ piège (R-CONT-05) : invisible pour les humains et les lecteurs d'écran, jamais atteint avec Tab */}
            <div
                aria-hidden="true"
                className="absolute -left-[9999px] h-0 w-0 overflow-hidden"
            >
                <label htmlFor="website">Website</label>
                <input
                    id="website"
                    tabIndex={-1}
                    autoComplete="off"
                    value={form.data.website}
                    onChange={(e) => form.setData('website', e.target.value)}
                />
            </div>

            <div>
                <label className="flex items-start gap-3 text-[0.95rem]">
                    <input
                        type="checkbox"
                        className="mt-1 size-5 shrink-0 accent-[var(--color-legno-700)]"
                        checked={form.data.privacy}
                        onChange={(e) =>
                            form.setData('privacy', e.target.checked)
                        }
                        aria-invalid={!!form.errors.privacy}
                        aria-describedby="privacy-error"
                    />
                    <span>
                        {t('contact.privacy')}
                        {privacyPage && (
                            <>
                                {' '}
                                <Link
                                    href={privacyPage.url}
                                    className="underline underline-offset-2"
                                >
                                    {t('contact.privacy_link')}
                                </Link>
                            </>
                        )}
                    </span>
                </label>
                {error('privacy')}
            </div>

            <button
                type="submit"
                disabled={form.processing}
                className="inline-flex min-h-12 items-center justify-center self-start rounded-full bg-legno-700 px-7 font-display font-semibold text-crema-50 transition-colors hover:bg-legno-800 disabled:opacity-60"
            >
                {form.processing ? t('contact.sending') : t('contact.send')}
            </button>
        </form>
    );
}
