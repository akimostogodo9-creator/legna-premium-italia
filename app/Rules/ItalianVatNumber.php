<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Partita IVA italienne (sprint 8) : 11 chiffres + chiffre de contrôle (algorithme officiel).
 * Accepte la saisie avec « IT » devant ou des espaces ; « 00000000000 » refusé.
 */
class ItalianVatNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid((string) $value)) {
            $fail(__('admin.settings.invalid_vat'));
        }
    }

    public static function normalize(string $value): string
    {
        return (string) preg_replace('/^IT/i', '', (string) preg_replace('/\s+/', '', $value));
    }

    public static function isValid(string $value): bool
    {
        $vat = self::normalize($value);

        if (! preg_match('/^\d{11}$/', $vat) || $vat === '00000000000') {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $digit = (int) $vat[$i];

            if ($i % 2 === 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return (10 - $sum % 10) % 10 === (int) $vat[10];
    }
}
