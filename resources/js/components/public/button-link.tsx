import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

type Variant = 'primary' | 'secondary' | 'whatsapp' | 'light' | 'ghost-light';

const styles: Record<Variant, string> = {
    // Action principale : brun bois (CDC §4.2)
    primary: 'bg-legno-700 text-crema-50 hover:bg-legno-800',
    // Action secondaire : contour
    secondary:
        'border border-legno-900/25 text-legno-900 hover:border-legno-900 hover:bg-crema-100',
    // WhatsApp : vert forêt, toujours secondaire (R-WA-05)
    whatsapp: 'bg-bosco-700 text-crema-50 hover:bg-bosco-800',
    // Action principale sur fond foncé (Hero sur photo, invitation finale, menu mobile)
    light: 'bg-crema-50 text-legno-900 hover:bg-crema-100',
    // Action secondaire sur fond foncé
    'ghost-light':
        'border border-crema-100/40 text-crema-50 hover:border-crema-50 hover:bg-crema-50/10',
};

type Props = {
    href: string;
    variant?: Variant;
    children: ReactNode;
    /** Lien externe (WhatsApp, mailto, tel) : balise <a> classique */
    external?: boolean;
    className?: string;
    onClick?: () => void;
};

// Boutons du site public : en pilule, police d'interface, grande zone de clic (≥ 44 px)
export function ButtonLink({
    href,
    variant = 'primary',
    children,
    external = false,
    className = '',
    onClick,
}: Props) {
    const classes = `inline-flex min-h-11 items-center justify-center gap-2 rounded-full px-6 py-2.5 font-display text-[0.98rem] font-semibold transition-colors duration-200 ${styles[variant]} ${className}`;

    if (external) {
        const isWeb = href.startsWith('http');

        return (
            <a
                href={href}
                className={classes}
                onClick={onClick}
                {...(isWeb
                    ? { target: '_blank', rel: 'noopener noreferrer' }
                    : {})}
            >
                {children}
            </a>
        );
    }

    return (
        <Link href={href} className={classes} onClick={onClick}>
            {children}
        </Link>
    );
}
