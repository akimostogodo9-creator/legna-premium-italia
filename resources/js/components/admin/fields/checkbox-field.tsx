import InputError from '@/components/input-error';

type Props = {
    id: string;
    label: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    error?: string;
    hint?: string;
};

// Case à cocher de l'admin (libellé traduisible, valeur jamais traduite)
export function CheckboxField({
    id,
    label,
    checked,
    onChange,
    error,
    hint,
}: Props) {
    return (
        <div className="grid gap-1">
            <label
                htmlFor={id}
                className="flex items-center gap-2 text-sm font-medium"
            >
                <input
                    id={id}
                    name={id}
                    type="checkbox"
                    checked={checked}
                    onChange={(event) => onChange(event.target.checked)}
                    className="size-4 rounded border-input"
                />
                {label}
            </label>
            {hint && !error && (
                <p className="text-sm text-muted-foreground">{hint}</p>
            )}
            <InputError message={error} />
        </div>
    );
}
