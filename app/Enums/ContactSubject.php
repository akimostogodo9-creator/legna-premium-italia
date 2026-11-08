<?php

namespace App\Enums;

// R-CONT-01 : sujets du formulaire de contact (renseignements uniquement)
enum ContactSubject: string
{
    case Products = 'products';
    case Delivery = 'delivery';
    case Prices = 'prices';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.contact_subject.'.$this->value);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
