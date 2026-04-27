<?php

namespace App\Modules\Auth\Tests\Unit\Domain\ValueObjects;
use App\Modules\Auth\Domain\ValueObjects\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_creates_valid_email(): void
    {
        $email = new Email('Test@Example.com');
        $this->assertSame('test@example.com', $email->value);
    }

    public function test_throws_on_invalid_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('invalid');
    }

    public function test_equals(): void
    {
        $a = new Email('foo@bar.com');
        $b = new Email('FOO@BAR.COM');
        $this->assertTrue($a->equals($b));
    }
}
