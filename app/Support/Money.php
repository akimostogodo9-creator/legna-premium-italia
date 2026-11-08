<?php

namespace App\Support;

use InvalidArgumentException;

// R-PRIX-02 : prix affichés en euros, au format italien (€ 1.234,56)
final class Money
{
    /** "1234.5" → "€ 1.234,50" ; null → null (le site affichera « Prezzo su richiesta »). */
    public static function format(?string $amount): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $normalized = self::normalize($amount);
        [$integer, $decimals] = explode('.', $normalized);
        $integer = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integer);

        return '€ '.$integer.','.$decimals;
    }

    /**
     * Saisie italienne de l'admin → valeur pour la base.
     * "1.234,56" → "1234.56" ; "129" → "129.00" ; "129,5" → "129.50" ; "" → null.
     */
    public static function parse(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $value = str_replace([' ', '€', '.'], '', trim($input));
        $value = str_replace(',', '.', $value);

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException("Prezzo non valido: {$input}");
        }

        return self::normalize($value);
    }

    /** "129" → "129.00" ; "129.5" → "129.50" (sans passer par un float). */
    private static function normalize(string $amount): string
    {
        if (! preg_match('/^\d+(\.\d+)?$/', $amount)) {
            throw new InvalidArgumentException("Importo non valido: {$amount}");
        }

        [$integer, $decimals] = array_pad(explode('.', $amount, 2), 2, '');
        $integer = ltrim($integer, '0') ?: '0';

        // On tronque au-delà de 2 décimales : la base n'en stocke jamais plus (DECIMAL(10,2))
        return $integer.'.'.str_pad(substr($decimals, 0, 2), 2, '0');
    }
}
