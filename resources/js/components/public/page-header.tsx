import type { ImageData } from '@/types/catalog';
import { ResponsiveImage } from './responsive-image';
import { RichText } from './rich-text';

type Props = { title: string; text?: string | null; image?: ImageData | null };

// En-tête des pages de contenu (sprint 8) : grand titre, introduction, photo facultative
export function PageHeader({ title, text, image }: Props) {
    return (
        <section className="bg-crema-100">
            <div
                className={`mx-auto grid max-w-7xl gap-10 px-5 pt-14 pb-14 md:px-8 md:pt-20 ${image ? 'lg:grid-cols-[1fr_1fr] lg:items-end' : ''}`}
            >
                <div className="min-w-0">
                    <h1 className="max-w-[16ch] text-[2.6rem] md:text-[4rem]">
                        {title}
                    </h1>
                    {text && (
                        <RichText
                            html={text}
                            className="mt-6 text-lg text-legno-700"
                        />
                    )}
                </div>
                {image && (
                    <ResponsiveImage
                        image={image}
                        priority
                        sizes="(min-width: 1024px) 50vw, 100vw"
                        className="aspect-[4/3] w-full rounded-[var(--radius-photo)] object-cover"
                    />
                )}
            </div>
        </section>
    );
}
