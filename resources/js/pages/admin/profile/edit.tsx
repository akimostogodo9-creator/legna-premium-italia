import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { TextField } from '@/components/admin/fields';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import profileRoutes from '@/routes/admin/profile';

type Props = { profile: { name: string; email: string } };

// F-AUTH-04 : mon profil (nom, e-mail, mot de passe) — remplace les pages de paramètres du kit
export default function ProfileEdit({ profile }: Props) {
    const t = useTranslations();
    const info = useForm(profile);
    const password = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    useUnsavedChangesWarning(info.isDirty && !info.processing);

    const saveInfo = (event: FormEvent) => {
        event.preventDefault();
        info.submit(profileRoutes.update(), { preserveScroll: true });
    };

    const savePassword = (event: FormEvent) => {
        event.preventDefault();
        password.submit(profileRoutes.password(), {
            preserveScroll: true,
            onSuccess: () => password.reset(),
        });
    };

    return (
        <>
            <Head title={t('profile.title')} />
            <div className="flex max-w-xl flex-col gap-10 p-4 md:p-8">
                <form onSubmit={saveInfo} className="flex flex-col gap-6">
                    <h1 className="text-2xl font-semibold">
                        {t('profile.title')}
                    </h1>
                    <TextField
                        id="name"
                        label={t('users.name')}
                        value={info.data.name}
                        onChange={(e) => info.setData('name', e.target.value)}
                        error={info.errors.name}
                        required
                    />
                    <TextField
                        id="email"
                        type="email"
                        label={t('users.email')}
                        value={info.data.email}
                        onChange={(e) => info.setData('email', e.target.value)}
                        error={info.errors.email}
                        required
                    />
                    <Button
                        type="submit"
                        className="self-start"
                        disabled={info.processing}
                    >
                        {t('actions.save')}
                    </Button>
                </form>

                <form
                    onSubmit={savePassword}
                    className="flex flex-col gap-6 border-t pt-8"
                >
                    <h2 className="text-lg font-semibold">
                        {t('profile.password_section')}
                    </h2>
                    <TextField
                        id="current_password"
                        type="password"
                        autoComplete="current-password"
                        label={t('profile.current_password')}
                        value={password.data.current_password}
                        onChange={(e) =>
                            password.setData('current_password', e.target.value)
                        }
                        error={password.errors.current_password}
                        required
                    />
                    <TextField
                        id="password"
                        type="password"
                        autoComplete="new-password"
                        label={t('profile.new_password')}
                        hint={t('auth.reset_description')}
                        value={password.data.password}
                        onChange={(e) =>
                            password.setData('password', e.target.value)
                        }
                        error={password.errors.password}
                        required
                    />
                    <TextField
                        id="password_confirmation"
                        type="password"
                        autoComplete="new-password"
                        label={t('profile.confirm_password')}
                        value={password.data.password_confirmation}
                        onChange={(e) =>
                            password.setData(
                                'password_confirmation',
                                e.target.value,
                            )
                        }
                        required
                    />
                    <Button
                        type="submit"
                        className="self-start"
                        disabled={password.processing}
                    >
                        {t('actions.save')}
                    </Button>
                </form>
            </div>
        </>
    );
}
