import { Head } from '@inertiajs/react';

type Props = {
    title: string;
    text: string;
};

// Page d'accueil provisoire (sprint 0) — vérifie le rendu serveur et la palette
export default function Home({ title, text }: Props) {
    return (
        <>
            <Head title={title} />
            <main className="flex min-h-screen items-center justify-center bg-crema-100 px-4">
                <div className="max-w-xl text-center">
                    <p className="text-sm font-semibold tracking-widest text-brace-500 uppercase">
                        Legna Premium Italia
                    </p>
                    <h1 className="mt-3 font-display text-4xl font-bold text-legno-900 md:text-5xl">
                        {title}
                    </h1>
                    <p className="mt-4 text-lg text-legno-700">{text}</p>
                    <span className="mt-8 inline-block rounded-full bg-bosco-700 px-5 py-2 text-sm text-crema-50">
                        Laravel + Inertia + React
                    </span>
                </div>
            </main>
        </>
    );
}
