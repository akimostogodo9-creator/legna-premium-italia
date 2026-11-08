import InputError from '@/components/input-error';
import { useTranslations } from '@/lib/i18n';
import { CheckboxField } from './fields';

type Props = {
    id: string;
    label: string;
    preview: string | null;
    hint?: string;
    error?: string;
    onFile: (file: File | null) => void;
    remove: boolean;
    onRemove: (remove: boolean) => void;
    provisional?: { checked: boolean; onChange: (v: boolean) => void };
};

// Champ image (sprint 7) : aperçu, nouveau fichier, suppression, marque « provvisoria »
export function ImageField({
    id,
    label,
    preview,
    hint,
    error,
    onFile,
    remove,
    onRemove,
    provisional,
}: Props) {
    const t = useTranslations();

    return (
        <div className="grid content-start gap-2">
            <label htmlFor={id} className="text-sm font-medium">
                {label}
            </label>
            {preview && !remove && (
                <img
                    src={preview}
                    alt=""
                    className="h-36 w-auto max-w-full rounded-md border object-cover"
                />
            )}
            <input
                id={id}
                type="file"
                accept="image/jpeg,image/png,image/webp"
                className="text-sm"
                onChange={(e) => onFile(e.target.files?.[0] ?? null)}
            />
            {hint && <p className="text-sm text-muted-foreground">{hint}</p>}
            {preview && (
                <CheckboxField
                    id={`${id}_remove`}
                    label={t('content.remove_image')}
                    checked={remove}
                    onChange={onRemove}
                />
            )}
            {provisional && (
                <CheckboxField
                    id={`${id}_provisional`}
                    label={t('content.image_provisional')}
                    checked={provisional.checked}
                    onChange={provisional.onChange}
                />
            )}
            <InputError message={error} />
        </div>
    );
}
