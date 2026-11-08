import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import users from '@/routes/admin/users';

type AdminUser = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    last_login_at: string | null;
    is_current: boolean;
};

// F-AUSR-01 : liste des administrateurs
export default function UsersIndex({ users: list }: { users: AdminUser[] }) {
    const t = useTranslations();
    const { errors } = usePage<{ errors: Record<string, string> }>().props;

    return (
        <>
            <Head title={t('users.title')} />
            <div className="flex flex-col gap-6 p-4 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold">
                        {t('users.title')}
                    </h1>
                    <Button asChild>
                        <Link href={users.create()}>
                            <Plus className="size-4" /> {t('users.new')}
                        </Link>
                    </Button>
                </div>

                <InputError message={errors.user} />

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3 font-medium">
                                    {t('users.name')}
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    {t('users.email')}
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    {t('users.status')}
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    {t('users.last_login')}
                                </th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody>
                            {list.map((user) => (
                                <tr key={user.id} className="border-t">
                                    <td className="px-4 py-3">
                                        <span translate="no">{user.name}</span>{' '}
                                        {user.is_current && (
                                            <span className="text-muted-foreground">
                                                {t('users.you')}
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3" translate="no">
                                        {user.email}
                                    </td>
                                    <td className="px-4 py-3">
                                        <span
                                            className={
                                                user.is_active
                                                    ? 'text-green-700'
                                                    : 'text-muted-foreground'
                                            }
                                        >
                                            {user.is_active
                                                ? t('users.active')
                                                : t('users.inactive')}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        {user.last_login_at ?? t('users.never')}
                                    </td>
                                    <td className="px-4 py-3">
                                        {!user.is_current && (
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    onClick={() =>
                                                        router.visit(
                                                            users.toggle(
                                                                user.id,
                                                            ),
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                >
                                                    {user.is_active
                                                        ? t(
                                                              'actions.deactivate',
                                                          )
                                                        : t('actions.activate')}
                                                </Button>
                                                <ConfirmDialog
                                                    title={t('actions.delete')}
                                                    trigger={
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                        >
                                                            {t(
                                                                'actions.delete',
                                                            )}
                                                        </Button>
                                                    }
                                                    onConfirm={() =>
                                                        router.visit(
                                                            users.destroy(
                                                                user.id,
                                                            ),
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                />
                                            </div>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
