import type { TextareaHTMLAttributes } from 'react';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';

type Props = TextareaHTMLAttributes<HTMLTextAreaElement> & {
    id: string;
    label: string;
    error?: string;
    hint?: string;
};

// Zone de texte de l'admin — translate="no" obligatoire (F-ADM-00b)
export function TextareaField({
    id,
    label,
    error,
    hint,
    rows = 4,
    ...props
}: Props) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            <textarea
                id={id}
                name={id}
                rows={rows}
                aria-invalid={!!error}
                className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none aria-invalid:border-red-500"
                {...props}
                translate="no"
            />
            {hint && !error && (
                <p className="text-sm text-muted-foreground">{hint}</p>
            )}
            <InputError message={error} />
        </div>
    );
}
