import { ContactCta } from '@/components/public/contact-cta';
import { ContentBlocks } from '@/components/public/content-blocks';
import type { ContentBlock } from '@/components/public/content-blocks';
import { PageHeader } from '@/components/public/page-header';
import { Seo } from '@/components/public/seo';
import { useTranslations } from '@/lib/i18n';
import type { ImageData } from '@/types/catalog';

type Props = {
    intro: {
        title: string;
        text?: string | null;
        image?: ImageData | null;
    } | null;
    blocks: ContentBlock[];
};

// F-PAGE-02 / CDC §14 (sprint 8) — zones, modalités, frais, délais, quantités, réception : seulement si remplis
export default function Delivery({ intro, blocks }: Props) {
    const t = useTranslations();
    const title = intro?.title ?? t('nav.delivery');

    return (
        <>
            <Seo title={title} image={intro?.image?.src} />
            <PageHeader title={title} text={intro?.text} image={intro?.image} />
            {blocks.length > 0 ? (
                <ContentBlocks blocks={blocks} />
            ) : (
                <p className="mx-auto max-w-7xl px-5 py-14 text-lg text-legno-700 md:px-8">
                    {t('pages.empty')}
                </p>
            )}
            <ContactCta />
        </>
    );
}
