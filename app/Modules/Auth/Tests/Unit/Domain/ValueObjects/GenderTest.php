<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    public function test_creates_valid_values(): void
    {
        foreach (['male', 'female'] as $value) {
            $gender = new Gender($value);
            $this->assertSame($value, $gender->getValue());
        }
    }

    public function test_normalizes_case(): void
    {
        $gender = new Gender('MALE');
        $this->assertSame('male', $gender->getValue());
    }

    public function test_throws_on_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Gender('unknown');
    }

    public function test_equals(): void
    {
        $a = new Gender('female');
        $b = new Gender('female');
        $this->assertTrue($a->equals($b));
    }

}
