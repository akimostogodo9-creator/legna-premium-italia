<?php

namespace App\Services\Ordering;

use App\Services\Settings\SettingsService;

// R-WA-01→04 : lien wa.me avec message prérempli ; null si aucun numéro.
// Prévu au sprint 6, avancé au sprint 3 pour le bouton flottant (le sprint 6 ajoutera le message produit).
class WhatsAppLinkBuilder
{
    public function __construct(private readonly SettingsService $settings) {}

    /** Lien général (bouton flottant, header, Contatti) ou null si pas de numéro. */
    public function general(): ?string
    {
        return $this->build($this->settings->get('whatsapp_message', ''));
    }

    /** Lien avec un message donné ; null si aucun numéro valide n'est configuré. */
    public function build(?string $message): ?string
    {
        $number = self::normalize($this->settings->get('whatsapp_number'));

        if ($number === null) {
            return null;
        }

        $url = 'https://wa.me/'.$number;

        return filled($message) ? $url.'?text='.rawurlencode($message) : $url;
    }

    /**
     * R-WA-02 : « +39 340 123 4567 » ou « 0039 340… » → « 393401234567 ».
     * Renvoie null si le résultat n'est pas un numéro international plausible (8 à 15 chiffres).
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return preg_match('/^[1-9]\d{7,14}$/', $digits) ? $digits : null;
    }
}
