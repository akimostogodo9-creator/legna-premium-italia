import type { ImageData } from '@/types/catalog';

type Props = {
    image: ImageData;
    /** Largeur affichée selon l'écran, ex. "(min-width: 1024px) 33vw, 100vw" */
    sizes: string;
    className?: string;
    /** Image visible au chargement (au-dessus de la ligne de flottaison) : pas de chargement différé */
    priority?: boolean;
};

// F-SEO-07 : srcset + dimensions + chargement différé
export function ResponsiveImage({
    image,
    sizes,
    className = '',
    priority = false,
}: Props) {
    return (
        <img
            src={image.src}
            srcSet={image.srcset || undefined}
            sizes={image.srcset ? sizes : undefined}
            width={image.width ?? undefined}
            height={image.height ?? undefined}
            alt={image.alt}
            loading={priority ? 'eager' : 'lazy'}
            decoding="async"
            fetchPriority={priority ? 'high' : undefined}
            className={className}
        />
    );
}
