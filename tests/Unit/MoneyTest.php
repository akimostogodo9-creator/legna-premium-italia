<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_format_italien(): void
    {
        $this->assertSame('€ 129,00', Money::format('129.00'));
        $this->assertSame('€ 1.234,50', Money::format('1234.5'));
        $this->assertSame('€ 1.234.567,89', Money::format('1234567.89'));
        $this->assertSame('€ 0,99', Money::format('0.99'));
    }

    public function test_prix_absent_renvoie_null(): void
    {
        $this->assertNull(Money::format(null));
        $this->assertNull(Money::format(''));
    }

    public function test_parse_saisie_italienne(): void
    {
        $this->assertSame('1234.56', Money::parse('1.234,56'));
        $this->assertSame('129.00', Money::parse('129'));
        $this->assertSame('129.50', Money::parse('129,5'));
        $this->assertSame('129.00', Money::parse('€ 129,00'));
        $this->assertNull(Money::parse(''));
        $this->assertNull(Money::parse(null));
    }

    public function test_parse_refuse_une_saisie_invalide(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::parse('dodici euro');
    }
}
