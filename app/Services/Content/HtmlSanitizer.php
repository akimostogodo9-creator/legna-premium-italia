<?php

namespace App\Services\Content;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonySanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * R-CONTENU-04 / étape 7 (XSS) : le HTML saisi dans l'éditeur est nettoyé À L'ENREGISTREMENT.
 * Liste blanche : seules ces balises survivent ; tout le reste (script, style, onclick…) est supprimé.
 */
class HtmlSanitizer
{
    private SymfonySanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('a', ['href'])
            ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer');

        $this->sanitizer = new SymfonySanitizer($config);
    }

    /** Renvoie null pour un contenu vide (évite de stocker « <p></p> »). */
    public function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $clean = trim($this->sanitizer->sanitize($html));

        return $this->isEmpty($clean) ? null : $clean;
    }

    private function isEmpty(string $html): bool
    {
        // « &nbsp; » décodé devient une espace insécable (U+00A0) : on la traite comme un blanc
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return trim(str_replace("\u{00A0}", ' ', $text)) === '';
    }
}
