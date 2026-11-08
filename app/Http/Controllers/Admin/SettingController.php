<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanySettingsRequest;
use App\Http\Requests\Admin\OrderSettingsRequest;
use App\Services\Ordering\TemplateRenderer;
use App\Services\Settings\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

// Paramètres : azienda (S8), contacts et modèles (S6), modulo di contatto (S9), catalogo/immagini/statistiche (S10)
class SettingController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function index(): Response
    {
        return Inertia::render('admin/settings/index', [
            'values' => [
                ...$this->settings->group('contatti'),
                ...$this->settings->group('ordina'),
            ],
            // Sprint 8 : données de l'entreprise (formulaire séparé)
            'company' => $this->settings->group('azienda'),
            // Sprint 9 : formulaire de contact (activation, limite, conservation)
            'form' => $this->settings->group('modulo'),
            // Sprint 10 : catalogue, images et statistiques
            'catalog' => [
                ...$this->settings->group('catalogo'),
                ...$this->settings->group('immagini'),
                ...$this->settings->group('statistiche'),
            ],
            'variables' => TemplateRenderer::VARIABLES,
            // Valeurs d'exemple pour l'aperçu uniquement (jamais affichées sur le site)
            'sample' => [
                'nome_prodotto' => 'Legna di faggio',
                'categoria' => 'Legna stagionata',
                'prezzo' => '€ 129,00',
                'unita' => 'bancale',
                'url_prodotto' => url('/prodotti/legna-di-faggio'),
            ],
        ]);
    }

    public function updateOrder(OrderSettingsRequest $request): RedirectResponse
    {
        $this->settings->setMany($request->validated());

        return back()->with('success', __('admin.messages.saved'));
    }

    /** F-ASET-01 (sprint 8) : données de l'entreprise */
    public function updateCompany(CompanySettingsRequest $request): RedirectResponse
    {
        $this->settings->setMany($request->validated());
        Cache::forget('legna.legal_pages');

        return back()->with('success', __('admin.messages.saved'));
    }

    /** F-ASET-06 (sprint 9) : paramètres du formulaire de contact */
    public function updateForm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'contact_form_enabled' => ['boolean'],
            'contact_rate_limit_per_hour' => ['required', 'integer', 'min:1', 'max:50'],
            'contact_retention_months' => ['required', 'integer', 'min:1', 'max:120'],
        ], [], [
            'contact_rate_limit_per_hour' => __('admin.settings.contact_rate_limit_per_hour'),
            'contact_retention_months' => __('admin.settings.contact_retention_months'),
        ]);

        $this->settings->setMany([...$data, 'contact_form_enabled' => $request->boolean('contact_form_enabled')]);

        return back()->with('success', __('admin.messages.saved'));
    }

    /** F-ASET-05 (sprint 10) : catalogue, images et statistiques */
    public function updateCatalog(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'products_per_page' => ['required', 'integer', 'min:3', 'max:48'],
            'featured_count' => ['required', 'integer', 'min:1', 'max:12'],
            // Ne pas dépasser upload_max_filesize du serveur (10 Mo sur ce poste)
            'image_max_mb' => ['required', 'integer', 'min:1', 'max:20'],
            'view_dedup_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
        ], [], collect(['products_per_page', 'featured_count', 'image_max_mb', 'view_dedup_minutes'])
            ->mapWithKeys(fn (string $key) => [$key => __('admin.settings.'.$key)])
            ->all());

        $this->settings->setMany([
            ...$data,
            'show_indicative_price_notice' => $request->boolean('show_indicative_price_notice'),
        ]);

        return back()->with('success', __('admin.messages.saved'));
    }
}
