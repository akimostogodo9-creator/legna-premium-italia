<?php

namespace Tests\Unit;

use App\Rules\ItalianVatNumber;
use PHPUnit\Framework\TestCase;

// F-ASET-01 : contrôle de la Partita IVA — sprint 8
class ItalianVatNumberTest extends TestCase
{
    public function test_numeros_valides(): void
    {
        $this->assertTrue(ItalianVatNumber::isValid('12345678903'));
        $this->assertTrue(ItalianVatNumber::isValid('IT 01234567897'));
        $this->assertTrue(ItalianVatNumber::isValid('00743110157'));
    }

    public function test_numeros_invalides(): void
    {
        $this->assertFalse(ItalianVatNumber::isValid('12345678901')); // chiffre de contrôle faux
        $this->assertFalse(ItalianVatNumber::isValid('1234567890'));  // 10 chiffres
        $this->assertFalse(ItalianVatNumber::isValid('00000000000'));
        $this->assertFalse(ItalianVatNumber::isValid('ABCDEFGHIJK'));
    }

    public function test_normalisation(): void
    {
        $this->assertSame('12345678903', ItalianVatNumber::normalize('IT 123 456 789 03'));
    }
}
