import type { Auth } from '@/types/auth';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

// Données partagées avec toutes les pages (HandleInertiaRequests::share) — sprint 2
declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            // Admin : { name } seulement ; site public : SiteData complet (types/site.ts)
            site: { name: string };
            translations: Record<string, unknown>;
            auth: Auth;
            flash: { success?: string | null; error?: string | null };
            /** Sprint 9 : admin uniquement (null sur le site public) */
            admin: { unread_messages: number } | null;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
