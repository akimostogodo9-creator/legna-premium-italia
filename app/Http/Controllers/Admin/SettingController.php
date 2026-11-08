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

// F-ASET-01 (azienda, sprint 8) et F-ASET-02/03/04/07 (contacts et modèles, sprint 6) — catalogo, modulo… au sprint 10
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
}
