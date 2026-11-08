import type { ImageData } from '@/types/catalog';
import { ResponsiveImage } from './responsive-image';
import { RichText } from './rich-text';

export type ContentBlock = {
    key: string;
    title: string;
    body: string | null;
    image?: ImageData | null;
};

/**
 * Blocs de contenu (Chi siamo, Consegna) — sprint 8 : titre à gauche, texte à droite ;
 * photo pleine largeur quand le bloc en a une. Alignement à gauche, lecture ≤ 68 caractères.
 */
export function ContentBlocks({ blocks }: { blocks: ContentBlock[] }) {
    return (
        <div className="mx-auto max-w-7xl px-5 md:px-8">
            {blocks.map((block) => (
                <section
                    key={block.key}
                    className="grid gap-6 border-t border-crema-200 py-14 first:border-t-0 md:py-20 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16"
                >
                    <h2 className="text-[1.9rem] md:text-[2.4rem]">
                        {block.title}
                    </h2>
                    <div className="min-w-0">
                        <RichText
                            html={block.body}
                            className="text-lg text-legno-900/90"
                        />
                        {block.image && (
                            <ResponsiveImage
                                image={block.image}
                                sizes="(min-width: 1024px) 55vw, 100vw"
                                className="mt-10 aspect-[16/10] w-full rounded-[var(--radius-photo)] object-cover"
                            />
                        )}
                    </div>
                </section>
            ))}
        </div>
    );
}
