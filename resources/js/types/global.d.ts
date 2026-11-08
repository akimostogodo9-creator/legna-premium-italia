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
            site: { name: string };
            translations: Record<string, unknown>;
            auth: Auth;
            flash: { success?: string | null; error?: string | null };
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
