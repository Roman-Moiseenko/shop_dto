<?php

namespace App\Modules\Storage\Tests\Unit\Domain\ValueObjects;

use App\Modules\Storage\Domain\ValueObjects\TagName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TagNameTest extends TestCase
{
    #[Test]
    public function creates_valid_name(): void
    {
        $tagName = new TagName('Summer 2026');
        $this->assertSame('Summer 2026', $tagName->getValue());
    }

    #[Test]
    public function trims_spaces(): void
    {
        $tagName = new TagName('  Sale  ');
        $this->assertSame('Sale', $tagName->getValue());
    }

    #[Test]
    public function throws_exception_on_too_short_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TagName('A');
    }

    #[Test]
    public function throws_exception_on_too_long_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TagName(str_repeat('x', 51));
    }

    #[Test]
    public function equals_works(): void
    {
        $a = new TagName('Promo');
        $b = new TagName('Promo');
        $c = new TagName('New');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
