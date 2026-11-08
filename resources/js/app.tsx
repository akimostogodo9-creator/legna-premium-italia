import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';

const appName = import.meta.env.VITE_APP_NAME || 'Legna Premium Italia';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // Layout choisi selon le dossier de la page :
    // public/ → aucun (layout public au sprint 3) ; auth/ → connexion ; le reste → admin (menu latéral)
    layout: (name) => {
        switch (true) {
            case name.startsWith('public/'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// Sprint 2 (§5.4) : plus de thème sombre automatique, l'admin reste en thème clair.
