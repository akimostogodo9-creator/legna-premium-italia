import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import { TextField } from '@/components/admin/fields';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import unitRoutes from '@/routes/admin/units';

type Unit = { id: number; name: string; products_count: number };

// Étape 2 §3.3 : unités de vente (bancale, quintale…) — modification au clic hors du champ
export default function UnitsIndex({ units }: { units: Unit[] }) {
    const t = useTranslations();
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const form = useForm({ name: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.submit(unitRoutes.store(), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <>
            <Head title={t('units.title')} />
            <div className="flex max-w-2xl flex-col gap-6 p-4 md:p-8">
                <h1 className="text-2xl font-semibold">{t('units.title')}</h1>
                <InputError message={errors.unit ?? errors.name} />

                <ul className="divide-y rounded-lg border">
                    {units.map((unit) => (
                        <li
                            key={unit.id}
                            className="flex items-center gap-3 p-3"
                        >
                            <input
                                defaultValue={unit.name}
                                translate="no"
                                aria-label={t('units.name')}
                                onBlur={(e) => {
                                    if (e.target.value !== unit.name) {
                                        router.visit(
                                            unitRoutes.update(unit.id),
                                            {
                                                data: { name: e.target.value },
                                                preserveScroll: true,
                                            },
                                        );
                                    }
                                }}
                                className="h-9 min-w-0 flex-1 rounded-md border border-input bg-transparent px-3 text-sm"
                            />
                            <span
                                className="text-sm text-muted-foreground"
                                title={t('units.products_count')}
                            >
                                {unit.products_count}
                            </span>
                            <ConfirmDialog
                                title={t('actions.delete')}
                                trigger={
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label={t('actions.delete')}
                                    >
                                        <Trash2 className="size-4" />
                                    </Button>
                                }
                                onConfirm={() =>
                                    router.visit(unitRoutes.destroy(unit.id), {
                                        preserveScroll: true,
                                    })
                                }
                            />
                        </li>
                    ))}
                </ul>

                <form onSubmit={submit} className="flex items-end gap-3">
                    <div className="flex-1">
                        <TextField
                            id="name"
                            label={t('units.new')}
                            placeholder={t('units.name')}
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            error={form.errors.name}
                            required
                        />
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        {t('actions.create')}
                    </Button>
                </form>
            </div>
        </>
    );
}
