import type { InputHTMLAttributes } from 'react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = InputHTMLAttributes<HTMLInputElement> & {
    id: string;
    label: string;
    error?: string;
    hint?: string;
};

// Champ texte de l'admin — translate="no" obligatoire (F-ADM-00b)
export function TextField({
    id,
    label,
    error,
    hint,
    className,
    ...props
}: Props) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            <Input
                id={id}
                name={id}
                aria-invalid={!!error}
                className={className}
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
