import { ContactCta } from '@/components/public/contact-cta';
import { FaqAccordion } from '@/components/public/faq-accordion';
import type { FaqItem } from '@/components/public/faq-accordion';
import { PageHeader } from '@/components/public/page-header';
import { Seo } from '@/components/public/seo';
import { useTranslations } from '@/lib/i18n';

type Props = { groups: { topic: string | null; faqs: FaqItem[] }[] };

// F-PAGE-03 / CDC §18 (sprint 8) : questions dépliables, groupées par thème
export default function Faq({ groups }: Props) {
    const t = useTranslations();
    const multiple = groups.length > 1;

    return (
        <>
            <Seo title={t('pages.faq_title')} />
            <PageHeader title={t('pages.faq_title')} />

            <div className="mx-auto max-w-4xl px-5 py-14 md:px-8 md:py-20">
                {groups.length === 0 && (
                    <p className="text-lg text-legno-700">
                        {t('pages.faq_empty')}
                    </p>
                )}
                {groups.map((group) => (
                    <section
                        key={group.topic ?? 'other'}
                        className="mb-14 last:mb-0"
                    >
                        {multiple && (
                            <h2 className="mb-4 text-[1.7rem]">
                                {group.topic ?? t('pages.faq_other')}
                            </h2>
                        )}
                        <FaqAccordion faqs={group.faqs} />
                    </section>
                ))}
            </div>

            <ContactCta />
        </>
    );
}
