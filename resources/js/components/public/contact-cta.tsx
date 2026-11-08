import { usePage } from '@inertiajs/react';
import { useTranslations } from '@/lib/i18n';
import type { PublicPageProps } from '@/types/site';
import { OrderButton } from './order-button';
import { WhatsAppButton } from './whatsapp-button';

// CDC §14 et §18 (sprint 8) : le visiteur peut toujours demander des précisions
export function ContactCta() {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;

    return (
        <section className="mx-auto max-w-7xl px-5 py-16 md:px-8 md:py-20">
            <div className="rounded-[var(--radius-card)] bg-crema-100 px-6 py-10 md:px-12">
                <h2 className="text-[1.9rem] md:text-[2.4rem]">
                    {t('pages.contact_cta_title')}
                </h2>
                <p className="mt-3 text-lg text-legno-700">
                    {t('pages.contact_cta_text')}
                </p>
                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                    {/* E-mail général si configuré, sinon « Contattaci » vers la page Contatti (géré par OrderButton) */}
                    <OrderButton order={site.general_order} />
                    <WhatsAppButton url={site.contacts.whatsapp_url} />
                </div>
            </div>
        </section>
    );
}
