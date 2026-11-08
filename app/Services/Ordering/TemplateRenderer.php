<?php

namespace App\Services\Ordering;

/**
 * R-ORDINA-07 : seules ces variables sont remplacées dans les modèles d'e-mail et de WhatsApp.
 * Toute autre accolade reste telle quelle (aucune exécution, aucune donnée cachée exposée).
 */
class TemplateRenderer
{
    public const VARIABLES = ['nome_prodotto', 'categoria', 'prezzo', 'unita', 'url_prodotto'];

    /** @param array<string, ?string> $values */
    public function render(string $template, array $values): string
    {
        $replacements = [];

        foreach (self::VARIABLES as $variable) {
            $replacements['{'.$variable.'}'] = (string) ($values[$variable] ?? '');
        }

        return strtr($template, $replacements);
    }

    /**
     * Variables utilisées dans le modèle mais non autorisées (validation de l'admin).
     *
     * @return list<string>
     */
    public function unknownVariables(string $template): array
    {
        preg_match_all('/\{([a-zA-Z_]+)\}/', $template, $matches);

        return array_values(array_diff(array_unique($matches[1]), self::VARIABLES));
    }
}
