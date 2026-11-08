import { RichText } from '@/components/public/rich-text';

export type PresentationContent = {
    title: string;
    text?: string | null;
    image?: string | null;
    image_alt?: string | null;
};

// F-HOME-02 : présentation de l'entreprise — texte à gauche, photo décalée à droite (si fournie)
export function Presentation({ content }: { content: PresentationContent }) {
    return (
        <section className="bg-crema-100">
            <div className="mx-auto grid max-w-7xl gap-12 px-5 py-20 md:px-8 md:py-28 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <h2 className="max-w-[16ch] text-[2.1rem] text-legno-900 md:text-[3rem]">
                        {content.title}
                    </h2>
                    <RichText
                        html={content.text ?? null}
                        className="mt-6 text-lg text-legno-900/85"
                    />
                </div>
                {content.image && (
                    <img
                        src={content.image}
                        alt={content.image_alt ?? ''}
                        loading="lazy"
                        className="aspect-[4/3] w-full rounded-[var(--radius-photo)] object-cover lg:translate-y-10"
                    />
                )}
            </div>
        </section>
    );
}
