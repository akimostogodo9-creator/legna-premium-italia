import { Head, usePage } from '@inertiajs/react';
import type { PublicPageProps } from '@/types/site';

type Props = {
    title?: string;
    description?: string | null;
    image?: string | null;
};

// F-SEO-02 : title, description, Open Graph (complété au sprint 11 : canonical, JSON-LD).
// Le suffixe « | Legna Premium Italia » du <title> est ajouté par app.tsx (pas ici, pour éviter le doublon).
export function Seo({ title, description, image }: Props) {
    const { site } = usePage<PublicPageProps>().props;
    const fullTitle = title ? `${title} | ${site.name}` : site.name;
    const desc = description ?? site.description ?? undefined;

    return (
        <Head title={title ?? ''}>
            {desc && (
                <meta
                    head-key="description"
                    name="description"
                    content={desc}
                />
            )}
            <meta head-key="og:title" property="og:title" content={fullTitle} />
            {desc && (
                <meta
                    head-key="og:description"
                    property="og:description"
                    content={desc}
                />
            )}
            <meta head-key="og:type" property="og:type" content="website" />
            <meta head-key="og:locale" property="og:locale" content="it_IT" />
            {image && (
                <meta head-key="og:image" property="og:image" content={image} />
            )}
        </Head>
    );
}
