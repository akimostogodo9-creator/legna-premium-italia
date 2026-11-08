import type { OrderLink } from './catalog';

// Données publiques partagées par HandleInertiaRequests (PublicSiteData) — sprint 3
export type SiteData = {
    name: string;
    description: string | null;
    logo: { light: string | null; dark: string | null };
    contacts: {
        email: string | null;
        phone: string | null;
        phone_href: string | null;
        whatsapp_url: string | null;
        hours: string | null;
    };
    company: {
        name: string | null;
        address: string | null;
        vat: string | null;
    };
    legal_pages: { title: string; url: string }[];
    /** Sprint 6 : « Contattaci » par e-mail ; null sans e-mail de commande */
    general_order: OrderLink | null;
};

export type PublicPageProps = { site: SiteData };
