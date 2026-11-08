<?php

namespace Tests\Unit;

use App\Services\Ordering\WhatsAppLinkBuilder;
use PHPUnit\Framework\TestCase;

// R-WA-02 : normalisation des numéros WhatsApp (sprint 3)
class WhatsAppNormalizeTest extends TestCase
{
    public function test_normalisation_des_numeros(): void
    {
        $this->assertSame('393401234567', WhatsAppLinkBuilder::normalize('+39 340 123 4567'));
        $this->assertSame('393401234567', WhatsAppLinkBuilder::normalize('0039 340-123-4567'));
        $this->assertSame('393401234567', WhatsAppLinkBuilder::normalize('(+39) 340.123.4567'));
    }

    public function test_numeros_invalides(): void
    {
        $this->assertNull(WhatsAppLinkBuilder::normalize(null));
        $this->assertNull(WhatsAppLinkBuilder::normalize(''));
        $this->assertNull(WhatsAppLinkBuilder::normalize('12345'));
        $this->assertNull(WhatsAppLinkBuilder::normalize('0612345678')); // indicatif manquant
    }
}
