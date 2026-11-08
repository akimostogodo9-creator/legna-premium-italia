import { useTranslations } from '@/lib/i18n';
import { CheckboxField, SelectField, TextareaField, TextField } from './fields';

export type Item = {
    icon: string;
    title: string;
    text: string | null;
    visible: boolean;
};

type Props = {
    name: string;
    items: Item[];
    icons: string[];
    onChange: (items: Item[]) => void;
    errors: Record<string, string>;
};

// R-CONTENU-02 (sprint 7) : jusqu'à 4 blocs « Perché sceglierci », chacun désactivable
export function ItemsEditor({ name, items, icons, onChange, errors }: Props) {
    const t = useTranslations();
    const rows: Item[] = Array.from(
        { length: 4 },
        (_, i) =>
            items[i] ?? {
                icon: icons[i] ?? icons[0],
                title: '',
                text: '',
                visible: true,
            },
    );

    const update = (index: number, patch: Partial<Item>) => {
        onChange(
            rows.map((row, i) => (i === index ? { ...row, ...patch } : row)),
        );
    };

    return (
        <div className="grid gap-4">
            <p className="text-sm text-muted-foreground">
                {t('content.items_hint')}
            </p>
            {rows.map((item, i) => (
                <fieldset key={i} className="grid gap-3 rounded-lg border p-4">
                    <legend className="px-1 text-sm font-semibold">
                        {t('content.item', { number: i + 1 })}
                    </legend>
                    <div className="grid gap-3 sm:grid-cols-[12rem_1fr]">
                        <SelectField
                            id={`${name}_${i}_icon`}
                            label={t('content.item_icon')}
                            value={item.icon}
                            options={icons.map((icon) => ({
                                value: icon,
                                label: t(`content.icons.${icon}`),
                            }))}
                            onChange={(e) =>
                                update(i, { icon: e.target.value })
                            }
                            error={errors[`${name}.${i}.icon`]}
                        />
                        <TextField
                            id={`${name}_${i}_title`}
                            label={t('content.fields.title')}
                            value={item.title}
                            onChange={(e) =>
                                update(i, { title: e.target.value })
                            }
                            error={errors[`${name}.${i}.title`]}
                        />
                    </div>
                    <TextareaField
                        id={`${name}_${i}_text`}
                        rows={2}
                        label={t('content.fields.text')}
                        value={item.text ?? ''}
                        onChange={(e) => update(i, { text: e.target.value })}
                        error={errors[`${name}.${i}.text`]}
                    />
                    <CheckboxField
                        id={`${name}_${i}_visible`}
                        label={t('content.item_visible')}
                        checked={item.visible}
                        onChange={(v) => update(i, { visible: v })}
                    />
                </fieldset>
            ))}
        </div>
    );
}
