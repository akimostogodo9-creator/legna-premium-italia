import { usePage } from '@inertiajs/react';
import { ButtonLink } from '@/components/public/button-link';
import { GrowthRings } from '@/components/public/growth-rings';
import { OrderButton } from '@/components/public/order-button';
import { WhatsAppIcon } from '@/components/public/whatsapp-icon';
import { useTranslations } from '@/lib/i18n';
import type { PublicPageProps } from '@/types/site';

export type FinalCtaContent = { title: string; text?: string | null };

// F-HOME-08 : invitation finale — e-mail général (sprint 6) et WhatsApp si configuré
export function FinalCta({ content }: { content: FinalCtaContent }) {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;

    return (
        <section className="relative overflow-hidden bg-bosco-700 text-crema-50">
            <GrowthRings className="pointer-events-none absolute top-1/2 -right-24 size-[36rem] -translate-y-1/2 text-crema-50/10" />
            <div className="relative mx-auto max-w-7xl px-5 py-20 md:px-8 md:py-24">
                <h2 className="max-w-[18ch] text-[2.1rem] md:text-[3.2rem]">
                    {content.title}
                </h2>
                {content.text && (
                    <p className="mt-5 max-w-xl text-lg text-crema-100/85">
                        {content.text}
                    </p>
                )}
                <div className="mt-10 flex flex-col gap-3 sm:flex-row">
                    {/* Sprint 6 : e-mail général préparé si configuré, sinon lien vers Contatti */}
                    <OrderButton order={site.general_order} onDark />
                    {site.contacts.whatsapp_url && (
                        <ButtonLink
                            href={site.contacts.whatsapp_url}
                            external
                            variant="ghost-light"
                        >
                            <WhatsAppIcon /> {t('actions.whatsapp')}
                        </ButtonLink>
                    )}
                </div>
            </div>
        </section>
    );
}
