<?php

namespace App\Services\Ordering;

use App\Models\Product;
use App\Services\Settings\SettingsService;
use App\Support\Money;

/**
 * ★ R-ORDINA-01→09 : le SERVEUR décide du contenu du message ; React ne fait qu'encoder et ouvrir.
 * Renvoie null si aucune adresse de commande n'est configurée (R-ORDINA-05 : bouton masqué).
 */
class OrderLinkBuilder
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly TemplateRenderer $renderer,
    ) {}

    /** @return array{email: string, subject: string, body: string, label: string, kind: string}|null */
    public function forProduct(Product $product): ?array
    {
        $email = $this->email();

        if ($email === null) {
            return null;
        }

        // R-ORDINA-09 : produit épuisé → modèle « disponibilité »
        $kind = $product->isOutOfStock() ? 'availability' : 'order';
        $values = $this->values($product);

        return [
            'email' => $email,
            'subject' => $this->renderer->render((string) $this->settings->get("{$kind}_subject_template", ''), $values),
            'body' => $this->renderer->render((string) $this->settings->get("{$kind}_body_template", ''), $values),
            'label' => __($kind === 'availability' ? 'public.order.ask_availability' : 'public.order.order'),
            'kind' => $kind,
        ];
    }

    /**
     * F-ORD-07 : message général (« Contattaci », invitation finale, page Contatti).
     *
     * @return array{email: string, subject: string, body: string, label: string, kind: string}|null
     */
    public function general(): ?array
    {
        $email = $this->email();

        if ($email === null) {
            return null;
        }

        return [
            'email' => $email,
            'subject' => (string) $this->settings->get('general_subject_template', ''),
            'body' => (string) $this->settings->get('general_body_template', ''),
            'label' => __('public.actions.contact_us'),
            'kind' => 'general',
        ];
    }

    /**
     * Valeurs des variables pour un produit (R-ORDINA-07).
     *
     * @return array<string, string>
     */
    public function values(Product $product): array
    {
        return [
            'nome_prodotto' => $product->name,
            'categoria' => $product->category?->name ?? '',
            'prezzo' => Money::format($product->price) ?? __('public.product.price_on_request'),
            'unita' => $product->price ? ($product->saleUnit?->name ?? '') : '',
            'url_prodotto' => route('products.show', $product->slug),
        ];
    }

    private function email(): ?string
    {
        $email = $this->settings->get('order_email');

        return filled($email) ? $email : null;
    }
}
