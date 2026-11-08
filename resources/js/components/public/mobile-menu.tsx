import { Link, usePage } from '@inertiajs/react';
import { X } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { isActive, publicNavigation } from '@/config/public-navigation';
import { useTranslations } from '@/lib/i18n';
import { contact } from '@/routes';
import type { PublicPageProps } from '@/types/site';
import { ButtonLink } from './button-link';
import { GrowthRings } from './growth-rings';
import { Logo } from './logo';
import { WhatsAppIcon } from './whatsapp-icon';

type Props = { open: boolean; onClose: () => void };

// F-LAY-02 : menu plein écran ; Échap ou un lien le ferme ; défilement de la page bloqué
export function MobileMenu({ open, onClose }: Props) {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;
    const { url } = usePage();
    const closeButton = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };
        document.addEventListener('keydown', onKey);
        document.body.style.overflow = 'hidden';
        closeButton.current?.focus();

        return () => {
            document.removeEventListener('keydown', onKey);
            document.body.style.overflow = '';
        };
    }, [open, onClose]);

    return (
        <div
            id="mobile-menu"
            role="dialog"
            aria-modal="true"
            aria-label={t('nav.main')}
            hidden={!open}
            className="fixed inset-0 z-50 overflow-hidden bg-legno-900 text-crema-100 lg:hidden"
        >
            <GrowthRings className="pointer-events-none absolute -right-32 -bottom-32 size-[28rem] text-brace-500/15" />

            <div className="relative flex h-20 items-center justify-between px-5">
                <Logo name={site.name} variant="dark" src={site.logo.dark} />
                <button
                    ref={closeButton}
                    type="button"
                    onClick={onClose}
                    className="inline-flex size-11 items-center justify-center rounded-full hover:bg-crema-100/10"
                >
                    <X className="size-6" />
                    <span className="sr-only">{t('nav.close')}</span>
                </button>
            </div>

            <nav className="relative px-5 pt-6">
                <ul className="flex flex-col">
                    {publicNavigation.map((item) => (
                        <li
                            key={item.href}
                            className="border-b border-crema-100/10"
                        >
                            <Link
                                href={item.href}
                                onClick={onClose}
                                aria-current={
                                    isActive(item.href, url)
                                        ? 'page'
                                        : undefined
                                }
                                className="block py-4 font-display text-3xl font-semibold [font-stretch:90%] aria-[current=page]:text-brace-500"
                            >
                                {t(item.labelKey)}
                            </Link>
                        </li>
                    ))}
                </ul>
            </nav>

            <div className="absolute inset-x-5 bottom-8 flex flex-col gap-3">
                <ButtonLink
                    href={contact().url}
                    onClick={onClose}
                    variant="light"
                    className="w-full"
                >
                    {t('actions.contact_us')}
                </ButtonLink>
                {site.contacts.whatsapp_url && (
                    <ButtonLink
                        href={site.contacts.whatsapp_url}
                        external
                        variant="whatsapp"
                        className="w-full"
                    >
                        <WhatsAppIcon /> {t('actions.whatsapp')}
                    </ButtonLink>
                )}
            </div>
        </div>
    );
}
