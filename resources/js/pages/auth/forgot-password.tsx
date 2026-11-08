import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/lib/i18n';
import { login } from '@/routes';
import { email } from '@/routes/password';

// « Password dimenticata » (/admin/password-dimenticata) — sprint 2, T9
export default function ForgotPassword({ status }: { status?: string }) {
    const t = useTranslations();

    return (
        <>
            <Head title={t('auth.forgot_title')} />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="space-y-6">
                <Form {...email.form()}>
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="email">{t('auth.email')}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="email"
                                    autoFocus
                                    placeholder={t('auth.email_placeholder')}
                                    translate="no"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="my-6 flex items-center justify-start">
                                <Button
                                    className="w-full"
                                    disabled={processing}
                                    data-test="email-password-reset-link-button"
                                >
                                    {processing && <Spinner />}
                                    {t('auth.send_link')}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="text-center text-sm text-muted-foreground">
                    <TextLink href={login()}>
                        {t('auth.back_to_login')}
                    </TextLink>
                </div>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'auth.forgot_title',
    description: 'auth.forgot_description',
};
