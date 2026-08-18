<?php

namespace Tests\Unit;

use App\Support\NumberFormatter;
use PHPUnit\Framework\TestCase;

class NumberFormatterTest extends TestCase
{
    public function test_parses_indonesian_thousands_separator(): void
    {
        $this->assertSame(10000.0, NumberFormatter::parseId('10.000'));
        $this->assertSame(1234567.0, NumberFormatter::parseId('1.234.567'));
        $this->assertSame(1500.0, NumberFormatter::parseId('1.500'));
    }

    public function test_parses_comma_decimal(): void
    {
        $this->assertSame(10558.16, NumberFormatter::parseId('10.558,16'));
        $this->assertSame(10000.5, NumberFormatter::parseId('10.000,50'));
        $this->assertSame(0.93, NumberFormatter::parseId('0,93'));
        $this->assertSame(12345.67, NumberFormatter::parseId('12.345,67'));
    }

    public function test_parses_plain_or_dot_decimal_input(): void
    {
        $this->assertSame(10000.0, NumberFormatter::parseId('10000'));
        $this->assertSame(10000.5, NumberFormatter::parseId('10000.5'));
        $this->assertSame(0.93, NumberFormatter::parseId('0.93'));
    }

    public function test_returns_null_for_invalid_input(): void
    {
        $this->assertNull(NumberFormatter::parseId(''));
        $this->assertNull(NumberFormatter::parseId(null));
        $this->assertNull(NumberFormatter::parseId('abc'));
        $this->assertNull(NumberFormatter::parseId('10.abc'));
        $this->assertNull(NumberFormatter::parseId('-'));
    }
}
