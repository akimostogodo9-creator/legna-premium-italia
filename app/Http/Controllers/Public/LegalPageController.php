<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Settings\SettingsService;
use Inertia\Inertia;
use Inertia\Response;

// F-PAGE-04 / R-LEG-02 (sprint 8) : une page non publiée OU vide renvoie 404
class LegalPageController extends Controller
{
    public const SLUGS = ['termini-e-condizioni', 'privacy-policy', 'cookie-policy', 'informazioni-azienda', 'reclami'];

    public function __invoke(string $slug, SettingsService $settings): Response
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('public/legal-page', [
            'page' => [
                'title' => $page->title,
                'content' => $page->content, // HTML nettoyé à l'enregistrement
                'meta_description' => $page->meta_description,
                'updated_at' => $page->content_updated_at?->format('d/m/Y'),
            ],
            // R-LEG-03 : sur « Informazioni sull'azienda », les données saisies dans les paramètres
            'company' => $slug === 'informazioni-azienda'
                ? array_filter([
                    'company_name' => $settings->get('company_name'),
                    'company_address' => $settings->get('company_address'),
                    'company_vat' => $settings->get('company_vat'),
                    'company_tax_code' => $settings->get('company_tax_code'),
                    'company_rea' => $settings->get('company_rea'),
                ])
                : [],
        ]);
    }
}
