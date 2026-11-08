import { ChevronLeft, ChevronRight, Expand } from 'lucide-react';
import { useRef, useState } from 'react';
import { useTranslations } from '@/lib/i18n';
import type { ImageData } from '@/types/catalog';
import { GrowthRings } from './growth-rings';
import { Lightbox } from './lightbox';
import { ResponsiveImage } from './responsive-image';

/**
 * F-PROD-02 / CDC §11 : grande image + miniatures, flèches, balayage au doigt,
 * flèches du clavier, agrandissement plein écran.
 */
export function ProductGallery({ images }: { images: ImageData[] }) {
    const t = useTranslations();
    const [index, setIndex] = useState(0);
    const [zoom, setZoom] = useState(false);
    const touchStart = useRef<number | null>(null);

    if (images.length === 0) {
        // Aucune photo (aperçu admin) : cernes du bois, jamais une image inventée
        return (
            <div className="aspect-[4/3] overflow-hidden rounded-[var(--radius-photo)] bg-legno-900">
                <GrowthRings className="size-full text-brace-500/40" />
            </div>
        );
    }

    const go = (delta: number) =>
        setIndex((i) => (i + delta + images.length) % images.length);
    const many = images.length > 1;

    const onTouchEnd = (x: number) => {
        if (touchStart.current === null) {
            return;
        }

        const dx = x - touchStart.current;

        if (Math.abs(dx) > 40) {
            go(dx < 0 ? 1 : -1);
        }

        touchStart.current = null;
    };

    return (
        <section
            aria-roledescription="carousel"
            aria-label={t('product.gallery')}
        >
            <div
                className="relative aspect-[4/3] overflow-hidden rounded-[var(--radius-photo)] bg-crema-100"
                onTouchStart={(e) => {
                    touchStart.current = e.touches[0].clientX;
                }}
                onTouchEnd={(e) => onTouchEnd(e.changedTouches[0].clientX)}
                onKeyDown={(e) => {
                    if (e.key === 'ArrowLeft') {
                        go(-1);
                    }

                    if (e.key === 'ArrowRight') {
                        go(1);
                    }
                }}
            >
                {images.map((image, i) => (
                    <ResponsiveImage
                        key={image.src}
                        image={image}
                        priority={i === 0}
                        sizes="(min-width: 1024px) 55vw, 100vw"
                        className={`absolute inset-0 size-full object-cover transition-opacity duration-300 ${i === index ? 'opacity-100' : 'opacity-0'}`}
                    />
                ))}

                <button
                    type="button"
                    onClick={() => setZoom(true)}
                    aria-label={t('product.enlarge')}
                    className="absolute top-4 right-4 inline-flex size-11 items-center justify-center rounded-full bg-crema-50/90 text-legno-900 hover:bg-crema-50"
                >
                    <Expand className="size-5" />
                </button>

                {many && (
                    <>
                        <button
                            type="button"
                            onClick={() => go(-1)}
                            aria-label={t('product.previous_photo')}
                            className="absolute top-1/2 left-3 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-crema-50/90 text-legno-900 hover:bg-crema-50"
                        >
                            <ChevronLeft className="size-6" />
                        </button>
                        <button
                            type="button"
                            onClick={() => go(1)}
                            aria-label={t('product.next_photo')}
                            className="absolute top-1/2 right-3 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-crema-50/90 text-legno-900 hover:bg-crema-50"
                        >
                            <ChevronRight className="size-6" />
                        </button>
                        <p
                            className="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-legno-900/75 px-3 py-1 font-display text-sm text-crema-50"
                            aria-live="polite"
                        >
                            {t('product.photo', {
                                current: index + 1,
                                total: images.length,
                            })}
                        </p>
                    </>
                )}
            </div>

            {many && (
                <ul className="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6">
                    {images.map((image, i) => (
                        <li key={image.src}>
                            <button
                                type="button"
                                onClick={() => setIndex(i)}
                                aria-label={t('product.photo', {
                                    current: i + 1,
                                    total: images.length,
                                })}
                                aria-current={i === index}
                                className={`block aspect-square w-full overflow-hidden rounded-xl ring-2 ring-offset-2 ring-offset-crema-50 transition ${i === index ? 'ring-legno-700' : 'opacity-70 ring-transparent hover:opacity-100'}`}
                            >
                                <ResponsiveImage
                                    image={image}
                                    sizes="96px"
                                    className="size-full object-cover"
                                />
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            {zoom && (
                <Lightbox
                    images={images}
                    index={index}
                    onIndexChange={setIndex}
                    onClose={() => setZoom(false)}
                />
            )}
        </section>
    );
}
