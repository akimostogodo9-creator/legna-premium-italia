<?php

namespace App\Services\Admin;

use App\Models\ContentSection;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Product;
use App\Services\Settings\SettingsService;

/**
 * F-DASH-04 / R-LEG-04 (sprint 10) : ce qui manque avant la mise en ligne.
 * Niveaux : « critical » (bloquant pour la mise en ligne), « warning » (à corriger), « info » (facultatif).
 */
class ConfigurationChecker
{
    /** Pages légales indispensables pour une activité commerciale en Italie (CDC §27). */
    private const REQUIRED_LEGAL = ['termini-e-condizioni', 'privacy-policy', 'cookie-policy', 'informazioni-azienda'];

    public function __construct(private readonly SettingsService $settings) {}

    /** @return list<array{key: string, level: string, message: string, link: string}> */
    public function alerts(): array
    {
        $alerts = [];
        $add = function (string $key, string $level, string $route, array $params = []) use (&$alerts) {
            $alerts[] = [
                'key' => $key,
                'level' => $level,
                'message' => __("admin.alerts.{$key}", $params),
                'link' => route($route, absolute: false),
            ];
        };

        // Contacts (R-ORDINA-05, R-WA-04)
        if (! $this->settings->filled('order_email')) {
            $add('order_email', 'critical', 'admin.settings.index');
        }
        if ($this->settings->get('contact_form_enabled', true) && ! $this->settings->filled('contact_email')) {
            $add('contact_email', 'warning', 'admin.settings.index');
        }
        if (! $this->settings->filled('whatsapp_number')) {
            $add('whatsapp', 'info', 'admin.settings.index');
        }

        // Entreprise (R-LEG-03)
        if (! $this->settings->filled('company_name') || ! $this->settings->filled('company_vat')) {
            $add('company', 'critical', 'admin.settings.index');
        }

        // Pages légales (R-LEG-04)
        $published = Page::published()->pluck('slug')->all();
        $missingRequired = Page::whereIn('slug', self::REQUIRED_LEGAL)->whereNotIn('slug', $published)->orderBy('id')->pluck('title')->all();
        $missingOptional = Page::whereNotIn('slug', [...self::REQUIRED_LEGAL, ...$published])->orderBy('id')->pluck('title')->all();
        if ($missingRequired !== []) {
            $add('legal_required', 'critical', 'admin.legal.index', ['pages' => implode(', ', $missingRequired)]);
        }
        if ($missingOptional !== []) {
            $add('legal_optional', 'warning', 'admin.legal.index', ['pages' => implode(', ', $missingOptional)]);
        }

        // Catalogue
        if (! Product::visible()->exists()) {
            $add('no_products', 'critical', 'admin.products.index');
        }
        if (($demo = Product::where('name', 'like', '[DEMO]%')->count()) > 0) {
            $add('demo_products', 'critical', 'admin.products.index', ['count' => $demo]);
        }
        if (($noPhoto = Product::doesntHave('images')->count()) > 0) {
            $add('products_no_photo', 'warning', 'admin.products.index', ['count' => $noPhoto]);
        }

        // Contenus
        if (($provisional = $this->provisionalImages()) > 0) {
            $add('provisional_images', 'warning', 'admin.content.index', ['count' => $provisional]);
        }
        if (($placeholders = Faq::where('answer', 'like', '%[DA COMPLETARE]%')->count()) > 0) {
            $add('faq_placeholders', 'info', 'admin.faq.index', ['count' => $placeholders]);
        }
        if (! $this->settings->filled('delivery_summary')) {
            $add('delivery_summary', 'info', 'admin.identity.edit');
        }
        if (! $this->settings->filled('logo_light')) {
            $add('logo', 'info', 'admin.identity.edit');
        }

        // Les plus importantes en premier (tri stable : l'ordre ci-dessus est gardé à niveau égal)
        $order = ['critical' => 0, 'warning' => 1, 'info' => 2];
        usort($alerts, fn (array $a, array $b) => $order[$a['level']] <=> $order[$b['level']]);

        return $alerts;
    }

    /** Vrai si rien de bloquant ne reste (utilisé au sprint 13 avant la mise en ligne). */
    public function readyForLaunch(): bool
    {
        return collect($this->alerts())->doesntContain('level', 'critical');
    }

    /** Images de contenu marquées « provvisoria » (sprint 7) et encore présentes. */
    private function provisionalImages(): int
    {
        return (int) ContentSection::all()->sum(function (ContentSection $section) {
            $content = $section->content ?? [];

            return collect($content)
                ->filter(fn ($value, $key) => str_ends_with((string) $key, '_provisional') && $value === true)
                ->keys()
                ->filter(fn (string $key) => ! empty($content[substr($key, 0, -strlen('_provisional'))]))
                ->count();
        });
    }
}
