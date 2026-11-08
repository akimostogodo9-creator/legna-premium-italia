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

// F-PAGE-01 / CDC §16 (sprint 8) — aucun texte inventé : seuls les blocs remplis par l'admin apparaissent
export default function About({ intro, blocks }: Props) {
    const t = useTranslations();
    const title = intro?.title ?? t('nav.about');

    return (
        <>
            <Seo title={title} image={intro?.image?.src} />
            <PageHeader title={title} text={intro?.text} image={intro?.image} />
            {blocks.length > 0 && <ContentBlocks blocks={blocks} />}
            <ContactCta />
        </>
    );
}
