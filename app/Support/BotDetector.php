<?php

namespace App\Support;

/**
 * R-STAT-04 (sprint 10) : les robots connus (moteurs de recherche, aperçus de liens, outils) ne sont pas comptés.
 * « whatsapp » et « telegram » : quand quelqu'un partage un lien, l'application télécharge la page
 * pour l'aperçu — ce n'est pas une visite.
 */
final class BotDetector
{
    private const PATTERN = '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|whatsapp|telegram|preview|headless|lighthouse|curl|wget|python-requests|httpclient/i';

    public static function isBot(?string $userAgent): bool
    {
        return $userAgent === null || $userAgent === '' || (bool) preg_match(self::PATTERN, $userAgent);
    }
}
