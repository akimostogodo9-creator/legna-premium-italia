<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Sprint 2 (§5.4) : thème clair uniquement (plus de détection du mode sombre) --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }
        </style>

        {{-- Sprint 7 (F-ACONT-05) : icône et image de partage envoyées depuis l'admin, sinon celles par défaut --}}
        @php
            $legnaSettings = app(\App\Services\Settings\SettingsService::class);
            $legnaDisk = \Illuminate\Support\Facades\Storage::disk('public');
            $legnaFavicon = $legnaSettings->get('favicon');
            $legnaShareImage = $legnaSettings->get('default_share_image');
        @endphp
        @if ($legnaFavicon)
            <link rel="icon" type="image/png" href="{{ $legnaDisk->url($legnaFavicon) }}">
            <link rel="apple-touch-icon" href="{{ $legnaDisk->url($legnaFavicon) }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @endif
        @if ($legnaShareImage && ! request()->is('admin', 'admin/*'))
            {{-- head-key « og:image » : une fiche produit remplace cette image par sa propre photo --}}
            <meta property="og:image" content="{{ $legnaDisk->url($legnaShareImage) }}" data-inertia="og:image">
        @endif

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
