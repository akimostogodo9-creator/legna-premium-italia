<?php

namespace App\Enums;

// R-CONT-04 : nouveau → lu → traité
enum ContactStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Handled = 'handled';

    public function label(): string
    {
        return __('enums.contact_status.'.$this->value);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
