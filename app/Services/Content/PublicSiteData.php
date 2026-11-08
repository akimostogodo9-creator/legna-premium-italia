<?php

namespace App\Services\Content;

use App\Models\Page;
use App\Services\Ordering\OrderLinkBuilder;
use App\Services\Ordering\WhatsAppLinkBuilder;
use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Données communes à toutes les pages publiques (header, footer, WhatsApp) — sprint 3.
 * R-CONTENU-03 : une coordonnée n'apparaît QUE si elle est renseignée.
 * ⚠️ Envoyé dans le code source de chaque page : uniquement des données publiques.
 */
class PublicSiteData
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly WhatsAppLinkBuilder $whatsapp,
        private readonly OrderLinkBuilder $orders,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $phone = $this->settings->get('company_phone');
        $email = $this->settings->get('contact_email');

        return [
            'name' => config('app.name'),
            'description' => $this->settings->get('footer_description'),
            'logo' => [
                'light' => $this->assetUrl($this->settings->get('logo_light')),
                'dark' => $this->assetUrl($this->settings->get('logo_dark')),
            ],
            'contacts' => [
                'email' => $email,
                'phone' => $phone,
                'phone_href' => $phone ? 'tel:'.preg_replace('/[^\d+]/', '', $phone) : null,
                'whatsapp_url' => $this->whatsapp->general(),
                'hours' => $this->settings->get('company_hours'),
            ],
            // R-LEG-03 : mentions légales seulement si saisies
            'company' => [
                'name' => $this->settings->get('company_name'),
                'address' => $this->settings->get('company_address'),
                'vat' => $this->settings->get('company_vat'),
            ],
            'legal_pages' => $this->legalPages(),
            // F-ORD-07 (sprint 6) : « Contattaci » par e-mail (invitation finale, page Contatti) ; null sans e-mail
            'general_order' => $this->orders->general(),
        ];
    }

    /**
     * R-LEG-02 : seules les pages légales publiées et non vides apparaissent dans le footer.
     * Cache de 10 min, vidé à chaque modification d'une page au sprint 8.
     *
     * @return list<array{title: string, url: string}>
     */
    private function legalPages(): array
    {
        return Cache::remember('legna.legal_pages', now()->addMinutes(10), fn () => Page::published()
            ->orderBy('id')
            ->get(['slug', 'title'])
            ->map(fn (Page $page) => ['title' => $page->title, 'url' => '/'.$page->slug])
            ->all());
    }

    private function assetUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
