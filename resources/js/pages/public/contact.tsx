import { usePage } from '@inertiajs/react';
import { Clock, Mail, MapPin, Phone, Truck } from 'lucide-react';
import type { ReactNode } from 'react';
import { ContactForm } from '@/components/public/contact-form';
import { OrderButton } from '@/components/public/order-button';
import { PageHeader } from '@/components/public/page-header';
import { Seo } from '@/components/public/seo';
import { WhatsAppButton } from '@/components/public/whatsapp-button';
import { useTranslations } from '@/lib/i18n';
import type { PublicPageProps } from '@/types/site';

type Props = {
    intro: { title: string; text?: string | null } | null;
    address: string | null;
    deliveryArea: string | null;
    formEnabled: boolean;
    subjects: { value: string; label: string }[];
};

// F-CONT-01/02 / CDC §17 (sprint 9) : seules les coordonnées renseignées apparaissent (R-CONTENU-03)
export default function Contact({
    intro,
    address,
    deliveryArea,
    formEnabled,
    subjects,
}: Props) {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;
    const { contacts } = site;
    const title = intro?.title ?? t('contact.title');

    const row = (icon: ReactNode, label: string, value: ReactNode) => (
        <div className="flex gap-4 border-t border-crema-200 py-5">
            <span className="mt-0.5 text-bosco-700" aria-hidden="true">
                {icon}
            </span>
            <div className="min-w-0">
                <p className="font-display text-sm font-medium text-legno-700">
                    {label}
                </p>
                <div className="mt-0.5 text-lg">{value}</div>
            </div>
        </div>
    );

    return (
        <>
            <Seo title={title} description={intro?.text} />
            <PageHeader title={title} text={intro?.text} plainText />

            <div className="mx-auto grid max-w-7xl gap-14 px-5 py-14 md:px-8 md:py-20 lg:grid-cols-[0.85fr_1.15fr] lg:gap-20">
                <section aria-labelledby="coordinates" className="min-w-0">
                    <h2 id="coordinates" className="sr-only">
                        {t('contact.title')}
                    </h2>
                    <div className="mb-8 flex flex-col gap-3 sm:flex-row">
                        {site.general_order && (
                            <OrderButton order={site.general_order} />
                        )}
                        <WhatsAppButton url={contacts.whatsapp_url} />
                    </div>
                    {contacts.email &&
                        row(
                            <Mail className="size-6" />,
                            t('contact.email'),
                            <a
                                href={`mailto:${contacts.email}`}
                                className="break-all underline-offset-4 hover:underline"
                            >
                                {contacts.email}
                            </a>,
                        )}
                    {contacts.phone &&
                        contacts.phone_href &&
                        row(
                            <Phone className="size-6" />,
                            t('contact.phone'),
                            <a
                                href={contacts.phone_href}
                                className="underline-offset-4 hover:underline"
                            >
                                {contacts.phone}
                            </a>,
                        )}
                    {contacts.hours &&
                        row(
                            <Clock className="size-6" />,
                            t('contact.hours'),
                            <span className="whitespace-pre-line">
                                {contacts.hours}
                            </span>,
                        )}
                    {address &&
                        row(
                            <MapPin className="size-6" />,
                            t('contact.address'),
                            <span className="whitespace-pre-line">
                                {address}
                            </span>,
                        )}
                    {deliveryArea &&
                        row(
                            <Truck className="size-6" />,
                            t('contact.delivery_area'),
                            <span className="whitespace-pre-line">
                                {deliveryArea}
                            </span>,
                        )}
                </section>

                <section
                    aria-labelledby="form-title"
                    className="min-w-0 rounded-[var(--radius-card)] bg-crema-100 p-6 md:p-10"
                >
                    <h2
                        id="form-title"
                        className="text-[1.9rem] md:text-[2.4rem]"
                    >
                        {t('contact.form_title')}
                    </h2>
                    {formEnabled ? (
                        <>
                            <p className="mt-3 mb-8 text-legno-700">
                                {t('contact.form_intro')}
                            </p>
                            <ContactForm subjects={subjects} />
                        </>
                    ) : (
                        <p className="mt-3 text-legno-700">
                            {t('contact.unavailable')}
                        </p>
                    )}
                </section>
            </div>
        </>
    );
}
