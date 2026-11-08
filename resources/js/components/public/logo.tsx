type Props = {
    /** 'light' = sur fond clair (brun), 'dark' = sur fond foncé (beige) */
    variant?: 'light' | 'dark';
    /** Afficher le nom à côté du symbole */
    withName?: boolean;
    /** Logo envoyé par l'admin (prioritaire sur le logo dessiné) — sprint 7 */
    src?: string | null;
    name: string;
    className?: string;
};

/** Symbole : coupe de bûche (cernes décentrés + fente du bois fendu). Validé par ISI (sprint 3). */
export function LogMark({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 48 48"
            aria-hidden="true"
            className={className}
            fill="none"
        >
            <circle
                cx="24"
                cy="24"
                r="21"
                stroke="currentColor"
                strokeWidth="3"
            />
            <ellipse
                cx="25.5"
                cy="23"
                rx="14.5"
                ry="13.5"
                stroke="currentColor"
                strokeWidth="2"
                opacity="0.85"
            />
            <ellipse
                cx="26.5"
                cy="22.5"
                rx="8.5"
                ry="8"
                stroke="currentColor"
                strokeWidth="2"
                opacity="0.7"
            />
            <circle cx="27" cy="22" r="2.6" fill="var(--color-brace-500)" />
            <path
                d="M27 22 L44 31"
                stroke="currentColor"
                strokeWidth="2.5"
                strokeLinecap="round"
            />
        </svg>
    );
}

// CDC §6 : logo moderne, lisible sur fond clair et foncé, intégré au header et au footer
export function Logo({
    variant = 'light',
    withName = true,
    src = null,
    name,
    className = '',
}: Props) {
    const color = variant === 'light' ? 'text-legno-900' : 'text-crema-100';

    if (src) {
        return (
            <img src={src} alt={name} className={`h-10 w-auto ${className}`} />
        );
    }

    return (
        <span
            className={`inline-flex items-center gap-2.5 ${color} ${className}`}
            translate="no"
        >
            <LogMark className="size-9 shrink-0 md:size-10" />
            {withName && (
                <span className="font-display text-lg leading-none font-bold tracking-tight [font-stretch:88%] md:text-xl">
                    {name}
                </span>
            )}
        </span>
    );
}
