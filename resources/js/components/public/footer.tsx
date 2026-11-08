import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { publicNavigation } from '@/config/public-navigation';
import { useTranslations } from '@/lib/i18n';
import { faq } from '@/routes';
import type { PublicPageProps } from '@/types/site';
import { GrowthRings } from './growth-rings';
import { Logo } from './logo';

// F-LAY-04 : 4 colonnes ; n'affiche que les informations renseignées (R-CONTENU-03, R-LEG-02/03)
export function Footer() {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;
    const { contacts, company } = site;
    const hasContacts =
        contacts.email || contacts.phone || contacts.whatsapp_url;
    const linkClass = 'text-crema-100/80 transition-colors hover:text-crema-50';

    return (
        <footer className="relative overflow-hidden bg-legno-900 text-crema-100">
            <GrowthRings className="pointer-events-none absolute -top-40 -right-40 size-[34rem] text-brace-500/10" />

            <div className="relative mx-auto grid max-w-7xl gap-12 px-5 py-16 md:grid-cols-2 md:px-8 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div className="max-w-sm">
                    <Logo
                        name={site.name}
                        variant="dark"
                        src={site.logo.dark}
                    />
                    {site.description && (
                        <p className="mt-5 text-[0.98rem] text-crema-100/75">
                            {site.description}
                        </p>
                    )}
                </div>

                <FooterColumn title={t('footer.navigation')}>
                    {publicNavigation.map((item) => (
                        <li key={item.href}>
                            <Link href={item.href} className={linkClass}>
                                {t(item.labelKey)}
                            </Link>
                        </li>
                    ))}
                </FooterColumn>

                <FooterColumn title={t('footer.information')}>
                    <li>
                        <Link href={faq()} className={linkClass}>
                            {t('footer.faq')}
                        </Link>
                    </li>
                    {site.legal_pages.map((page) => (
                        <li key={page.url}>
                            <Link href={page.url} className={linkClass}>
                                {page.title}
                            </Link>
                        </li>
                    ))}
                </FooterColumn>

                {hasContacts && (
                    <FooterColumn title={t('footer.contacts')}>
                        {contacts.email && (
                            <li>
                                <a
                                    href={`mailto:${contacts.email}`}
                                    className={`${linkClass} break-all`}
                                >
                                    {contacts.email}
                                </a>
                            </li>
                        )}
                        {contacts.phone && contacts.phone_href && (
                            <li>
                                <a
                                    href={contacts.phone_href}
                                    className={linkClass}
                                >
                                    {contacts.phone}
                                </a>
                            </li>
                        )}
                        {contacts.whatsapp_url && (
                            <li>
                                <a
                                    href={contacts.whatsapp_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className={linkClass}
                                >
                                    WhatsApp
                                </a>
                            </li>
                        )}
                        {contacts.hours && (
                            <li className="whitespace-pre-line text-crema-100/60">
                                {contacts.hours}
                            </li>
                        )}
                    </FooterColumn>
                )}
            </div>

            <div className="relative border-t border-crema-100/10">
                <div className="mx-auto flex max-w-7xl flex-col gap-1 px-5 py-6 font-display text-sm text-crema-100/60 md:flex-row md:justify-between md:px-8">
                    <p>
                        © {new Date().getFullYear()} {company.name ?? site.name}
                        . {t('footer.rights')}
                    </p>
                    {(company.address || company.vat) && (
                        <p>
                            {company.address}
                            {company.address && company.vat && ', '}
                            {company.vat && `${t('footer.vat')} ${company.vat}`}
                        </p>
                    )}
                </div>
            </div>
        </footer>
    );
}

function FooterColumn({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <div>
            <h2 className="font-display text-base font-semibold text-crema-50">
                {title}
            </h2>
            <ul className="mt-4 flex flex-col gap-2.5 text-[0.98rem]">
                {children}
            </ul>
        </div>
    );
}
