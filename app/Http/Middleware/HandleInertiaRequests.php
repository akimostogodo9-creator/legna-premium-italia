<?php

namespace App\Http\Middleware;

use App\Models\ContactMessage;
use App\Services\Content\PublicSiteData;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Données envoyées à TOUTES les pages (étape 5 §6) — sprint 2.
     * ⚠️ Tout ce qui est ici est visible dans le code source de la page : jamais de secret.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $isAdmin = $request->routeIs('admin.*') || $request->is('admin', 'admin/*');

        return [
            ...parent::share($request),

            // Sprint 3 : données publiques (header, footer, WhatsApp) — inutiles dans l'admin
            'site' => fn () => $isAdmin
                ? ['name' => config('app.name')]
                : app(PublicSiteData::class)->toArray(),

            // Textes d'interface de l'espace courant (lang/it/admin.php ou public.php)
            'translations' => fn () => trans($isAdmin ? 'admin' : 'public'),

            // Uniquement les champs nécessaires de l'admin connecté (jamais le modèle entier)
            'auth' => [
                'user' => fn () => $request->user()?->only(['id', 'name', 'email']),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],

            // Sprint 9 : badge « messages non lus » du menu de l'admin (rien côté site public)
            'admin' => fn () => $isAdmin && $request->user()
                ? ['unread_messages' => ContactMessage::unread()->count()]
                : null,

            // État du menu latéral (kit)
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
