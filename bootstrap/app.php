<?php

use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Services\Content\PublicSiteData;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Administration : /admin/... (noms de routes : admin.*) — T6, sprint 0
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        // Sprint 2 : l'admin reste en thème clair → plus de middleware « apparence » du kit
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // R-ADM-02 : déconnecte un administrateur désactivé (sprint 2)
        $middleware->alias([
            'admin.active' => EnsureAdminIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // F-LAY-08 (sprint 3) : pages d'erreur au design du site, en italien.
        // En local et en test, les erreurs 500 gardent la page de débogage de Laravel.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();
            $handled = app()->environment(['local', 'testing']) ? [403, 404] : [403, 404, 500, 503];

            if (! in_array($status, $handled, true) || $request->expectsJson() || $request->is('admin', 'admin/*')) {
                return $response;
            }

            // Adresse inconnue → aucune route, donc le middleware Inertia n'a pas tourné :
            // on fournit nous-mêmes les données du layout public (sans session).
            return Inertia::render('public/error', [
                'status' => $status,
                'site' => app(PublicSiteData::class)->toArray(),
                'translations' => trans('public'),
                'auth' => ['user' => null],
                'flash' => ['success' => null, 'error' => null],
            ])->toResponse($request)->setStatusCode($status);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
