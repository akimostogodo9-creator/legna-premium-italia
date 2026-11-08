<?php

namespace App\Enums;

// R-CAR-03 : disponibilité d'un produit (pas de gestion de stock)
enum Availability: string
{
    case Available = 'available';
    case OnOrder = 'on_order';
    case OutOfStock = 'out_of_stock';

    /** Libellé italien affiché sur le site et dans l'admin. */
    public function label(): string
    {
        return __('enums.availability.'.$this->value);
    }

    /** Liste des valeurs (utilisée par les migrations et la validation). */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
