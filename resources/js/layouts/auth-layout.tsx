import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';
import { useTranslations } from '@/lib/i18n';

// Sprint 2 (T9) : « title » et « description » sont des clés de traduction
// (ex. 'auth.login_title'), car Page.layout est statique et ne peut pas appeler t().
export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: React.ReactNode;
}) {
    const t = useTranslations();

    return (
        <AuthLayoutTemplate
            title={title && t(title)}
            description={description && t(description)}
        >
            {children}
        </AuthLayoutTemplate>
    );
}
