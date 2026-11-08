<?php

namespace App\Providers;

use App\Services\Settings\SettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Une seule instance des paramètres par requête (sprint 1)
        $this->app->singleton(SettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // Étape 7 (sprint 2) : 12 caractères minimum, lettres et chiffres, partout.
        // En production, le kit ajoute majuscules, symboles et vérification des fuites connues.
        Password::defaults(function (): Password {
            $rule = Password::min(12)->letters()->numbers();

            return app()->isProduction()
                ? $rule->mixedCase()->symbols()->uncompromised()
                : $rule;
        });
    }
}
