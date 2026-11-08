import { usePage } from '@inertiajs/react';
import { CheckCircle2, XCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

type Flash = { success?: string | null; error?: string | null };

// Affiche « Salvato con successo » ou une erreur après une action (F-ADM-00c).
// Le message disparaît après 5 secondes. Chaque réponse du serveur envoie un nouvel objet
// « flash » : le même texte deux fois de suite s'affiche donc bien deux fois.
export function FlashMessages() {
    const { flash } = usePage<{ flash: Flash }>().props;
    const [hidden, setHidden] = useState<Flash | null>(null);

    const message = flash?.success ?? flash?.error;

    useEffect(() => {
        if (!message) {
            return;
        }

        const timer = setTimeout(() => setHidden(flash), 5000);

        return () => clearTimeout(timer);
    }, [flash, message]);

    if (!message || hidden === flash) {
        return null;
    }

    const isError = !flash?.success;

    return (
        <div
            role="status"
            className={`fixed right-4 bottom-4 z-50 flex max-w-sm items-start gap-3 rounded-lg border px-4 py-3 shadow-lg ${
                isError
                    ? 'border-red-200 bg-red-50 text-red-800'
                    : 'border-green-200 bg-green-50 text-green-800'
            }`}
        >
            {isError ? (
                <XCircle className="mt-0.5 size-5 shrink-0" />
            ) : (
                <CheckCircle2 className="mt-0.5 size-5 shrink-0" />
            )}
            <p className="text-sm">{message}</p>
        </div>
    );
}
