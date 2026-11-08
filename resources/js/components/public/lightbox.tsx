import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { useTranslations } from '@/lib/i18n';
import type { ImageData } from '@/types/catalog';

type Props = {
    images: ImageData[];
    index: number;
    onIndexChange: (i: number) => void;
    onClose: () => void;
};

/** Plus grande taille disponible (dernier élément du srcset : 1600 px). */
function largest(image: ImageData): string {
    return image.srcset
        ? (image.srcset.split(', ').at(-1)?.split(' ')[0] ?? image.src)
        : image.src;
}

// Photo en plein écran : Échap ferme, flèches du clavier, balayage au doigt
export function Lightbox({ images, index, onIndexChange, onClose }: Props) {
    const t = useTranslations();
    const close = useRef<HTMLButtonElement>(null);
    const touchStart = useRef<number | null>(null);
    const go = (delta: number) =>
        onIndexChange((index + delta + images.length) % images.length);

    // Focus sur « Chiudi » et page bloquée, une seule fois à l'ouverture
    useEffect(() => {
        close.current?.focus();
        document.body.style.overflow = 'hidden';

        return () => {
            document.body.style.overflow = '';
        };
    }, []);

    // Clavier : relié à la photo courante
    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                onClose();
            }

            if (e.key === 'ArrowLeft') {
                onIndexChange((index - 1 + images.length) % images.length);
            }

            if (e.key === 'ArrowRight') {
                onIndexChange((index + 1) % images.length);
            }
        };
        document.addEventListener('keydown', onKey);

        return () => document.removeEventListener('keydown', onKey);
    }, [index, images.length, onClose, onIndexChange]);

    const image = images[index];

    return (
        <div
            role="dialog"
            aria-modal="true"
            aria-label={t('product.gallery')}
            className="fixed inset-0 z-50 flex items-center justify-center bg-legno-900/95 p-4"
            onClick={onClose}
            onTouchStart={(e) => {
                touchStart.current = e.touches[0].clientX;
            }}
            onTouchEnd={(e) => {
                if (touchStart.current === null) {
                    return;
                }

                const dx = e.changedTouches[0].clientX - touchStart.current;

                if (Math.abs(dx) > 40) {
                    go(dx < 0 ? 1 : -1);
                }

                touchStart.current = null;
            }}
        >
            <img
                src={largest(image)}
                alt={image.alt}
                className="max-h-full max-w-full rounded-xl object-contain"
                onClick={(e) => e.stopPropagation()}
            />

            <button
                ref={close}
                type="button"
                onClick={onClose}
                aria-label={t('product.close')}
                className="absolute top-4 right-4 inline-flex size-12 items-center justify-center rounded-full bg-crema-50/10 text-crema-50 hover:bg-crema-50/20"
            >
                <X className="size-6" />
            </button>
            {images.length > 1 && (
                <>
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            go(-1);
                        }}
                        aria-label={t('product.previous_photo')}
                        className="absolute left-4 inline-flex size-12 items-center justify-center rounded-full bg-crema-50/10 text-crema-50 hover:bg-crema-50/20"
                    >
                        <ChevronLeft className="size-7" />
                    </button>
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            go(1);
                        }}
                        aria-label={t('product.next_photo')}
                        className="absolute right-4 inline-flex size-12 items-center justify-center rounded-full bg-crema-50/10 text-crema-50 hover:bg-crema-50/20"
                    >
                        <ChevronRight className="size-7" />
                    </button>
                </>
            )}
        </div>
    );
}
