import type { ReactNode } from 'react';
import { CookieBanner } from '@/components/public/cookie-banner';
import { Footer } from '@/components/public/footer';
import { Header } from '@/components/public/header';
import { WhatsAppFloating } from '@/components/public/whatsapp-floating';
import { useTranslations } from '@/lib/i18n';

// Layout de toutes les pages publiques (étape 5 §3) — la classe « site » applique la charte.
// Appliqué automatiquement par app.tsx aux pages « public/… » (les pages ne l'ajoutent pas elles-mêmes) :
// le header et le footer restent en place d'une page à l'autre.
export default function PublicLayout({ children }: { children: ReactNode }) {
    const t = useTranslations();

    return (
        <div className="site flex min-h-screen flex-col">
            <a
                href="#contenuto"
                className="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-full focus:bg-legno-900 focus:px-4 focus:py-2 focus:text-crema-50"
            >
                {t('nav.skip')}
            </a>
            <Header />
            <main id="contenuto" className="flex-1">
                {children}
            </main>
            <Footer />
            <WhatsAppFloating />
            <CookieBanner />
        </div>
    );
}
