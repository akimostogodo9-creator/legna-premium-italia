import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { TextField } from '@/components/admin/fields';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import users from '@/routes/admin/users';

// F-AUSR-02 : création d'un administrateur (il choisit son mot de passe via l'e-mail reçu)
export default function UsersCreate() {
    const t = useTranslations();
    const form = useForm({ name: '', email: '' });

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.submit(users.store());
    };

    return (
        <>
            <Head title={t('users.new')} />
            <form
                onSubmit={submit}
                className="flex max-w-xl flex-col gap-6 p-4 md:p-8"
            >
                <h1 className="text-2xl font-semibold">{t('users.new')}</h1>

                <TextField
                    id="name"
                    label={t('users.name')}
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    error={form.errors.name}
                    required
                    autoFocus
                />
                <TextField
                    id="email"
                    type="email"
                    label={t('users.email')}
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                    error={form.errors.email}
                    required
                />

                <div className="flex gap-3">
                    <Button type="submit" disabled={form.processing}>
                        {t('actions.create')}
                    </Button>
                    <Button variant="secondary" asChild>
                        <Link href={users.index()}>{t('actions.cancel')}</Link>
                    </Button>
                </div>
            </form>
        </>
    );
}
