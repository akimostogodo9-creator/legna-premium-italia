import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useState, useSyncExternalStore } from 'react';
import { isActive, publicNavigation } from '@/config/public-navigation';
import { useTranslations } from '@/lib/i18n';
import { contact, home } from '@/routes';
import type { PublicPageProps } from '@/types/site';
import { ButtonLink } from './button-link';
import { Logo } from './logo';
import { MobileMenu } from './mobile-menu';
import { WhatsAppIcon } from './whatsapp-icon';

// Position de défilement lue sans état React (pas de setState dans un effet) ;
// côté serveur (SSR), la page est considérée « en haut ».
function subscribeToScroll(callback: () => void) {
    window.addEventListener('scroll', callback, { passive: true });

    return () => window.removeEventListener('scroll', callback);
}

function useScrolled(): boolean {
    return useSyncExternalStore(
        subscribeToScroll,
        () => window.scrollY > 12,
        () => false,
    );
}

// F-LAY-01/02/03 : header professionnel, compact au défilement, AUCUN panier (CDC §5)
export function Header() {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;
    const { url } = usePage();
    const scrolled = useScrolled();
    const [menuOpen, setMenuOpen] = useState(false);

    return (
        <header
            className={`sticky top-0 z-40 border-b transition-[background-color,box-shadow,border-color] duration-300 ${
                scrolled
                    ? 'border-crema-200 bg-crema-50/95 shadow-[var(--shadow-card)] backdrop-blur'
                    : 'border-transparent bg-crema-50'
            }`}
        >
            <div
                className={`mx-auto flex max-w-7xl items-center gap-6 px-5 transition-[height] duration-300 md:px-8 ${
                    scrolled ? 'h-16' : 'h-20'
                }`}
            >
                <Link href={home()} className="shrink-0" aria-label={site.name}>
                    <Logo name={site.name} src={site.logo.light} />
                </Link>

                <nav
                    aria-label={t('nav.main')}
                    className="ml-auto hidden lg:block"
                >
                    <ul className="flex items-center gap-1 font-display text-[0.98rem] font-medium">
                        {publicNavigation.map((item) => {
                            const active = isActive(item.href, url);

                            return (
                                <li key={item.href}>
                                    <Link
                                        href={item.href}
                                        aria-current={
                                            active ? 'page' : undefined
                                        }
                                        className={`relative rounded-full px-3.5 py-2 transition-colors hover:text-legno-700 ${
                                            active
                                                ? 'text-legno-900 after:absolute after:inset-x-3.5 after:-bottom-0.5 after:h-0.5 after:rounded-full after:bg-brace-500'
                                                : 'text-legno-900/75'
                                        }`}
                                    >
                                        {t(item.labelKey)}
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                <div className="ml-auto flex items-center gap-2 lg:ml-4">
                    {site.contacts.whatsapp_url && (
                        <a
                            href={site.contacts.whatsapp_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label={t('actions.whatsapp')}
                            className="hidden size-11 items-center justify-center rounded-full text-bosco-700 transition-colors hover:bg-bosco-700 hover:text-crema-50 sm:inline-flex"
                        >
                            <WhatsAppIcon />
                        </a>
                    )}
                    <ButtonLink
                        href={contact().url}
                        className="hidden sm:inline-flex"
                    >
                        {t('actions.contact_us')}
                    </ButtonLink>
                    <button
                        type="button"
                        onClick={() => setMenuOpen(true)}
                        aria-expanded={menuOpen}
                        aria-controls="mobile-menu"
                        className="inline-flex size-11 items-center justify-center rounded-full text-legno-900 hover:bg-crema-100 lg:hidden"
                    >
                        <Menu className="size-6" />
                        <span className="sr-only">{t('nav.menu')}</span>
                    </button>
                </div>
            </div>

            <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
        </header>
    );
}
