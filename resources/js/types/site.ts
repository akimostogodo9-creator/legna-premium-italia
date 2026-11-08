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
};

export type PublicPageProps = { site: SiteData };
