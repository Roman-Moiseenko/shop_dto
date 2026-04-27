<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_creates_valid_russian_phone(): void
    {
        $phone = new PhoneNumber('+7 999 123 45 67');
        $this->assertSame('+79991234567', $phone->getValue());
        $this->assertSame(7, $phone->getCountryCode());
    }

    public function test_creates_with_default_region(): void
    {
        $phone = new PhoneNumber('89991234567', 'RU');
        $this->assertSame('+79991234567', $phone->getValue());
    }

    public function test_throws_on_invalid_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhoneNumber('12345');
    }

    public function test_equals(): void
    {
        $a = new PhoneNumber('+7 (999) 123-45-67');
        $b = new PhoneNumber('89991234567');
        $this->assertTrue($a->equals($b));
    }
}
